<?php

namespace Modules\BlogModule\Repositories;

interface PostRepository
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
