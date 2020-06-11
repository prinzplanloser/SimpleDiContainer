<?php

namespace SimpleDi;

interface ContainerInterface
{
    public function get($key);

    public function has($key);

}