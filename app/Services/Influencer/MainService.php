<?php

namespace App\Services\Influencer;

use BcMath\Number;

class MainService{
    public $model;
    public function get(){
        return $this->model->get();
    }

    public function find(int $id){
        return $this->model->find($id);
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id)
    {
        $model = $this->model->findOrFail($id);
        return $model->delete();
    }

    public function firstWhere(string $column, $value)
    {
        return $this->model->where($column, $value)->first();
    }
    
    public function where(array $conditions)
    {
        return $this->model->where($conditions)->get();
    }
}