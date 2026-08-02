<?php
declare(strict_types=1);
namespace App\Core;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use InvalidArgumentException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, $concrete = null, bool $singleton = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function get(string $id): mixed
    {
        if ($this->has($id)) {
            $binding = $this->bindings[$id];
            if ($binding['singleton'] && isset($this->instances[$id])) {
                return $this->instances[$id];
            }
            $instance = $this->resolve($binding['concrete']);
            if ($binding['singleton']) {
                $this->instances[$id] = $instance;
            }
            return $instance;
        }
        return $this->autowire($id);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    private function resolve($concrete): object
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }
        if (is_string($concrete)) {
            return $this->autowire($concrete);
        }
        throw new InvalidArgumentException('Unresolvable binding');
    }

    private function autowire(string $class): object
    {
        try {
            $reflection = new ReflectionClass($class);
            if (!$reflection->isInstantiable()) {
                throw new InvalidArgumentException("Class {$class} is not instantiable");
            }
            $constructor = $reflection->getConstructor();
            if ($constructor === null) {
                return $reflection->newInstance();
            }
            $parameters = $constructor->getParameters();
            $dependencies = [];
            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                if ($type === null || $type->isBuiltin()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new InvalidArgumentException(
                            "Cannot resolve parameter {$parameter->getName()} of {$class}"
                        );
                    }
                } else {
                    $dependencies[] = $this->get($type->getName());
                }
            }
            return $reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Autowire failed for {$class}: " . $e->getMessage());
        }
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
        $this->bind($abstract, get_class($instance), true);
    }
}
