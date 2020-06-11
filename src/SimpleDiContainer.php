<?php

namespace SimpleDi;

use App\Components\DI\Exceptions\ContainerException;
use App\Components\DI\Exceptions\ServiceNotFoundException;

class SimpleDiContainer implements ContainerInterface {
    private $services;
    private $serviceStore;
    private $config;

    public function __construct(array $config = [], array $services = []) {
        $this->services = $services;
        $this->config = $config;
        $this->serviceStore = [];
    }

    public function set($key, $name) {
        $this->services[$key] = $name;
    }

    public function setConfig(array $config) {
        $this->config = $config;
    }

    public function get($key) {

        if (!$this->has($key)) {
            throw new ServiceNotFoundException('Service not found: ' . $key);
        }
        if (empty($this->serviceStore[$key])) {
            $this->serviceStore[$key] = $this->createService($key);
        }
        return $this->serviceStore[$key];
    }

    public function has($key) {
        return isset($this->services[$key]);
    }

    private function createService($key) {
        $entity = $this->services[$key];
        if (!class_exists($entity)) {
            throw new ContainerException('Class: ' . $entity . 'not exist');
        }
        $reflector = new \ReflectionClass($entity);
        $args = $this->getArgs($reflector);
        return $reflector->newInstanceArgs($args);

    }

    private function getArgs(\ReflectionClass $reflectionClass): array
    {
        $constructor = $reflectionClass->getConstructor();
        if (empty($constructor)) {
            return [];
        }
        $constructorParams = $constructor->getParameters();
        $args = [];
        foreach ($constructorParams as $param) {
            $dependencyName = $param->getClass()->getName();
            if (class_exists($dependencyName)) {
                $args[] = $dependencyName;
                continue;
            }
            $reflectionName = $reflectionClass->getShortName();
            $indicatedDependencies = $this->checkConfig($reflectionName);
            if (!$indicatedDependencies) {
                throw new ContainerException('Error config file settings');
            }
            foreach ($indicatedDependencies as $dependencyType => $dependencyName) {
                $args[] = $dependencyName;
            }
        }
        foreach ($args as $key => $className) {
            $reflectionArgs[] = new $className();
        }

        return $reflectionArgs;
    }

    private function checkConfig($id): array {
        return $this->config[$id];
    }
}