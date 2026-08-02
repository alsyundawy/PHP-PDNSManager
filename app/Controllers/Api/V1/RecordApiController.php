<?php
declare(strict_types=1);
namespace App\Controllers\Api\V1;
use App\Core\Request;
use App\Core\Response;
use App\Services\DNS\RecordService;
use App\Core\Exceptions\ValidationException;

class RecordApiController
{
    private RecordService $recordService;
    public function __construct(RecordService $recordService)
    {
        $this->recordService = $recordService;
    }
    public function index(Request $request, string $zoneId): Response
    {
        $records = $this->recordService->getRecordsForZone($zoneId);
        return (new Response())->json(['data' => $records]);
    }
    public function store(Request $request, string $zoneId): Response
    {
        try {
            $data = $request->getParsedBody();
            $result = $this->recordService->createRecord($zoneId, $data);
            return (new Response())->json(['data' => $result], 201);
        } catch (ValidationException $e) {
            return (new Response())->json(['error' => $e->getErrors()], 422);
        }
    }
    public function update(Request $request, string $zoneId, string $recordName, string $recordType): Response
    {
        try {
            $data = $request->getParsedBody();
            $result = $this->recordService->updateRecord($zoneId, $recordName, $recordType, $data);
            return (new Response())->json(['data' => $result]);
        } catch (ValidationException $e) {
            return (new Response())->json(['error' => $e->getErrors()], 422);
        }
    }
    public function destroy(Request $request, string $zoneId, string $recordName, string $recordType): Response
    {
        $this->recordService->deleteRecord($zoneId, $recordName, $recordType);
        return (new Response())->json(['message' => 'Record deleted'], 204);
    }
    public function bulk(Request $request, string $zoneId): Response
    {
        $data = $request->getParsedBody();
        if (empty($data['rrsets'])) {
            return (new Response())->json(['error' => 'rrsets required'], 422);
        }
        $result = $this->recordService->bulkUpdate($zoneId, $data['rrsets']);
        return (new Response())->json(['data' => $result]);
    }
}
