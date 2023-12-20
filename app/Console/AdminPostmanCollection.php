<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Pluralizer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class AdminPostmanCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:AdminPostmanCollection {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Postman Collection';

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->getSourceFilePath();
        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->info("File : {$path} already exits");
        }
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../../stubs/admin.postman.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     *
     */
    public function getStubVariables()
    {
        return [
            'MODEL_NAME'        => $this->argument('name'),
            'PATH'              => strtolower($this->getPluralClassName($this->argument('name'))),
            'COLUMNS'           => $this->getModelCols($this->argument('name'))
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub, $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        return base_path('postman/Admin') . '/' . Str::studly($this->getPluralClassName($this->argument('name'))) . 'PostmanCollection.json';
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Return the Plural Lower Name
     * @param $name
     * @return string
     */
    public function getPluralClassName($name)
    {
        return strtolower(Str::plural($name));
    }


    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    /**
     * Get Models Cols.
     *
     * @param  string  $ModelName
     * @return string
     */
    protected function getModelCols($ModelName)
    {
        $model = ('\\App\\Models\\' . $ModelName);
        $instance = new $model;
        $cols = $instance->getFillable();
        $data = [];
        foreach ($cols as $col) {
            if (($col == 'active') || ($col == 'created_at') || ($col == 'updated_at') || ($col == 'deleted_at')) {
                continue;
            }
            $key = [
                "key" => $col,
				"value" => "",
				"type" => "text"
            ];
            array_push($data,$key);
        }
        return (string)json_encode($data);
    }
}
