<?php
namespace App\Services;

use App\Models\File;

class FileService
{
    /**
     * Add New File
     * @param $record
     * @param $fileName
     * @param $path
     * @param $label
     * @param $relationName
     * @param $notes
     * @param $order
     */
    public function addFile($record,$fileName, $path, $label, $relationName, $notes = null, $order = null):void
    {
        $file = new File();
        $file->file_name = $fileName;
        $file->path = $path;
        $file->label = $label;

        if($notes){
            $file->notes = $notes;
        }

        if($order){
            $file->order = $order;
        }

        $record->$relationName()->save($file);
    }

    /**
     * Add New File
     * @param $record
     * @param $fileName
     * @param $path
     * @param $label
     * @param $relationName
     * @param $notes
     * @param $order
     */
    public function deleteFile($fileName, $path, $label, $modelId):void
    {
        $filePath = explode('/',$fileName);
        $file = end($filePath);

        $file = File::where('fileable_id',$modelId)
        ->whereFileName($file)
        ->whereLabel($label)
        ->wherePath($path)
        ->delete();
    }
    
}