<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use App\Enums\CommonEnums;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;
    protected $uploads;
    protected $listResource;
    protected $singleResource;
    protected $createRequest;

    public function __construct(Model $model, $uploads, $listResource, $singleResource, $createRequest)
    {
        $this->model = $model;
        $this->uploads = $uploads;
        $this->listResource = $listResource;
        $this->singleResource = $singleResource;
        $this->createRequest = $createRequest;
    }

    /**
     * List All Records.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(?bool $isPaginate, ?bool $onlyActive)
    {
        try {
            if ($onlyActive) {
                $this->model->whereIsActive(true);
            }
            if ($isPaginate) {
                $records = $this->model->paginate(CommonEnums::Paginate);
                $paginationData = [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'items_per_page' => $records->perPage(),
                    'total_records' => $records->total(),

                ];
            } else {
                $records = $this->model->get();
            }

            return apiResponse(
                true,
                '',
                200,
                $this->listResource::collection($records),
                isset($paginationData) ? $paginationData : null
            );
        } catch (\Throwable $th) {
            Log::error($th);
            return apiResponse(
                false,
                'Some Thing Went Wrong !',
                500
            );
        }
    }

    /**
     * Create a New Record
     * @param $createRequest
     * @param $relations = null
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($createRequest, $relations = null)
    {
        try {
            $record = $this->model::create($createRequest->all());

            if (!empty($this->uploads)) {
                foreach ($this->uploads as $index) {
                    if ($createRequest->has($index)) {
                        $fileName = uploadFile($createRequest[$index], $this->model->uploadDirectory);
                        $fileService = new FileService();
                        $fileService->addFile($record, $fileName, $this->model->uploadDirectory, $index, $this->model->fileRelationName);
                    }
                }
            }

            if (!empty($relations)) {
                $this->addRelationRecords($relations, $record, $createRequest);
            }

            return apiResponse(
                true,
                'Create Success',
                200,
                $record
            );
        } catch (\Throwable $th) {
            Log::error($th);
            return apiResponse(
                false,
                'Some Thing Went Wrong !',
                500
            );
        }
    }

    /**
     * Get Single Record
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {

            $record = $this->model::find($id);

            if (!$record) {
                return apiResponse(
                    true,
                    'Not Found !',
                    400,
                );
            }

            return apiResponse(
                true,
                '',
                200,
                new $this->singleResource($record)
            );
        } catch (\Throwable $th) {
            Log::error($th);
            return apiResponse(
                false,
                'Some Thing Went Wrong !',
                500
            );
        }
    }

    /**
     * Update Record
     * @param $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id, $request, $relations = null)
    {
        try {
            $record = $this->model::find($id);

            if (!$record) {
                return apiResponse(
                    true,
                    'Not Found !',
                    400,
                );
            }

            $record->update($request->all());
            $fileRelation = $this->model->fileRelationName;

            if (!empty($this->uploads)) {
                foreach ($this->uploads as $index) {
                    if ($request->has($index)) {
                        // delete old files
                        if ($record->$fileRelation) {
                            deleteFile($record->$fileRelation->file_name, $this->model->uploadDirectory);
                            $fileService = new FileService();
                            $fileService->deleteFile($record->$fileRelation->file_name, $this->model->uploadDirectory, $index, $id);
                        }

                        // add new files
                        $fileName = uploadFile($request[$index], $this->model->uploadDirectory);
                        $fileService = new FileService();
                        $fileService->addFile($record, $fileName, $this->model->uploadDirectory, $index, $this->model->fileRelationName);
                    }
                }
            }

            if (!empty($relations)) {
                $this->addRelationRecords($relations, $record, $request);
            }

            return apiResponse(
                true,
                'Update Success',
                200,
                $record
            );
        } catch (\Throwable $th) {
            Log::error($th);
            return apiResponse(
                false,
                'Some Thing Went Wrong !',
                500
            );
        }
    }

    /**
     * Delete Record
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $record = $this->model::find($id);
            $fileRelation = $this->model->fileRelationName;

            if (!$record) {
                return apiResponse(
                    true,
                    'Not Found !',
                    400,
                );
            }

            if (!empty($this->uploads)) {
                foreach ($this->uploads as $index) {
                    // delete old files
                    if ($record->$fileRelation) {
                        deleteFile($record->$fileRelation->file_name, $this->model->uploadDirectory);
                        $fileService = new FileService();
                        $fileService->deleteFile($record->$fileRelation->file_name, $this->model->uploadDirectory, $index, $id);
                    }
                }
            }

            $record->delete();

            return apiResponse(
                true,
                trans('Delete Success'),
                200,
            );
        } catch (\Throwable $th) {
            Log::error($th);
            return apiResponse(
                false,
                'Some Thing Went Wrong !',
                500
            );
        }
    }

    /**
     * add Relation Records
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    private function addRelationRecords($relations, $parentRecord, $request)
    {
        try {
            foreach ($relations as $type => $relation) {

                switch ($type) {
                    case 'manyToMany':
                        foreach ($relation as $singleRelation) {
                            $parentRecord->$singleRelation()->sync($request[$singleRelation]);
                        }
                        break;
                    case 'oneToMany':
                        foreach ($relation as $singleRelation) {
                            $relationName = $singleRelation['relationName'];
                            $foreignKey = $singleRelation['foreignKey'];
                            $foreignKeyId = $parentRecord->id;
                            $this->model = $singleRelation['model'];
                            $this->uploads = $singleRelation['uploads'] ?? [];

                            if (!$request->has($relationName)) {
                                break;
                            }
                            foreach ($request->$relationName as $record) {
                                $record[$foreignKey] = $foreignKeyId;
                                $this->store(collect($record));
                            }
                        }

                        break;

                    default:
                        break;
                }
            }
            return true;
        } catch (\Throwable $th) {
            Log::error($th);
            return apiResponse(
                false,
                'Some Thing Went Wrong !',
                500
            );
        }
    }
}
