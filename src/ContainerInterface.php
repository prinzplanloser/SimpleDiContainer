<?php

namespace SimpleDi;

interface ContainerInterface
{
    public function get(string $key);

    public function has(string $key);

}