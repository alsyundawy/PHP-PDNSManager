<?php

declare(strict_types=1);

namespace App\Services\DNS;

use App\Core\Database;
use App\Core\Logger;
use App\Models\ZoneTemplate;
use App\Models\ZoneTemplateRecord;
use PDO;

class ZoneTemplateService
{
    private Database $db;
    private Logger $logger;

    public function __construct(Database $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * @return ZoneTemplate[]
     */
    public function getAllTemplates(): array
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM zone_templates ORDER BY is_default DESC, name ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $templates = [];
            foreach ($rows as $row) {
                $templates[] = $this->hydrateTemplate($row);
            }
            return $templates;
        } catch (\Throwable $e) {
            $this->logger->error('Error getting zone templates: ' . $e->getMessage());
            return [];
        }
    }

    public function getTemplateById(int $id): ?ZoneTemplate
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM zone_templates WHERE id = ? LIMIT 1', [$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }
            $template = $this->hydrateTemplate($row);
            $template->records = $this->getRecordsForTemplate($id);
            return $template;
        } catch (\Throwable $e) {
            $this->logger->error('Error getting zone template by id: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return ZoneTemplateRecord[]
     */
    public function getRecordsForTemplate(int $templateId): array
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM zone_template_records WHERE template_id = ? ORDER BY type ASC, name ASC', [$templateId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'hydrateRecord'], $rows);
        } catch (\Throwable $e) {
            $this->logger->error('Error getting records for template: ' . $e->getMessage());
            return [];
        }
    }

    public function createTemplate(array $data, array $records = []): ?ZoneTemplate
    {
        $name = trim((string) ($data['name'] ?? ''));
        $description = isset($data['description']) ? trim((string) $data['description']) : null;
        $isDefault = !empty($data['is_default']) ? 1 : 0;

        if ($isDefault === 1) {
            $this->db->execute('UPDATE zone_templates SET is_default = 0');
        }

        $this->db->execute(
            'INSERT INTO zone_templates (name, description, is_default, created_at, updated_at) VALUES (?, ?, ?, datetime("now"), datetime("now"))',
            [$name, $description, $isDefault]
        );

        $templateId = (int) $this->db->lastInsertId();

        foreach ($records as $rec) {
            $this->addRecordToTemplate($templateId, $rec);
        }

        return $this->getTemplateById($templateId);
    }

    public function addRecordToTemplate(int $templateId, array $rec): void
    {
        $name = trim((string) ($rec['name'] ?? '@'));
        $type = strtoupper(trim((string) ($rec['type'] ?? 'A')));
        $content = trim((string) ($rec['content'] ?? ''));
        $ttl = (int) ($rec['ttl'] ?? 3600);
        $priority = isset($rec['priority']) && $rec['priority'] !== '' ? (int) $rec['priority'] : null;

        $this->db->execute(
            'INSERT INTO zone_template_records (template_id, name, type, content, ttl, priority, created_at) VALUES (?, ?, ?, ?, ?, ?, datetime("now"))',
            [$templateId, $name, $type, $content, $ttl, $priority]
        );
    }

    public function deleteTemplate(int $id): bool
    {
        $this->db->execute('DELETE FROM zone_template_records WHERE template_id = ?', [$id]);
        $this->db->execute('DELETE FROM zone_templates WHERE id = ?', [$id]);
        return true;
    }

    public function applyTemplateToZone(int $templateId, string $zoneName, RecordService $recordService): array
    {
        $template = $this->getTemplateById($templateId);
        if ($template === null) {
            throw new \InvalidArgumentException("Zone template ID {$templateId} not found");
        }

        $applied = [];
        $zoneNameClean = rtrim($zoneName, '.') . '.';

        foreach ($template->records as $record) {
            $recName = $record->name;
            if ($recName === '@') {
                $fullName = $zoneNameClean;
            } elseif (!str_ends_with($recName, '.')) {
                $fullName = $recName . '.' . $zoneNameClean;
            } else {
                $fullName = $recName;
            }

            $content = str_replace(
                ['@', '{zone}'],
                [rtrim($zoneNameClean, '.'), rtrim($zoneNameClean, '.')],
                $record->content
            );

            try {
                $recordData = [
                    'name' => $fullName,
                    'type' => $record->type,
                    'ttl' => $record->ttl,
                    'changetype' => 'REPLACE',
                    'records' => [
                        [
                            'content' => $content,
                            'disabled' => false,
                        ],
                    ],
                ];
                $recordService->createRecord($zoneNameClean, $recordData);
                $applied[] = [
                    'name' => $fullName,
                    'type' => $record->type,
                    'content' => $content,
                    'status' => 'success',
                ];
            } catch (\Throwable $e) {
                $this->logger->error("Failed to apply template record: {$fullName} {$record->type}: " . $e->getMessage());
                $applied[] = [
                    'name' => $fullName,
                    'type' => $record->type,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $applied;
    }

    private function hydrateTemplate(array $row): ZoneTemplate
    {
        $template = new ZoneTemplate();
        $template->id = (int) ($row['id'] ?? 0);
        $template->name = (string) ($row['name'] ?? '');
        $template->description = isset($row['description']) ? (string) $row['description'] : null;
        $template->is_default = (bool) ($row['is_default'] ?? false);
        $template->created_at = isset($row['created_at']) ? (string) $row['created_at'] : null;
        $template->updated_at = isset($row['updated_at']) ? (string) $row['updated_at'] : null;
        return $template;
    }

    private function hydrateRecord(array $row): ZoneTemplateRecord
    {
        $rec = new ZoneTemplateRecord();
        $rec->id = (int) ($row['id'] ?? 0);
        $rec->template_id = (int) ($row['template_id'] ?? 0);
        $rec->name = (string) ($row['name'] ?? '@');
        $rec->type = (string) ($row['type'] ?? 'A');
        $rec->content = (string) ($row['content'] ?? '');
        $rec->ttl = (int) ($row['ttl'] ?? 3600);
        $rec->priority = isset($row['priority']) ? (int) $row['priority'] : null;
        $rec->created_at = isset($row['created_at']) ? (string) $row['created_at'] : null;
        return $rec;
    }
}
