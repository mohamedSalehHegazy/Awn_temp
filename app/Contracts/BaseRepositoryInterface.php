<?php

namespace App\Contracts;

interface BaseRepositoryInterface
{
    public function index(?bool $isPaginate, ?bool $onlyActive);

    public function store(array $data, array $relations = null);

    public function show(int $id);

    public function update(int $id, array $data, $relations = null);

    public function destroy(int $id);

}
