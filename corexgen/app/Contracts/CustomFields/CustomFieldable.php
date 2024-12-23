<?php 

namespace App\Contracts\CustomFields;

interface CustomFieldable
{
    public function getCustomFieldEntityType(): string;
    public function customFields();
}