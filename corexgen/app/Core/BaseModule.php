<?php

abstract class BaseModule
{
    protected $id;
    protected $version;
    protected $dependencies = [];

    abstract public function boot(): void;
    abstract public function register(): void;

    public function hasPermission(string $permission): bool
    {
        // Check if current tenant has permission for this module feature
        return true;
    }
}
