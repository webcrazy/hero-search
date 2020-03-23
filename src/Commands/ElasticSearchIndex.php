<?php

namespace CarroPublic\HeroSearch\Commands;

use Exception;
use Illuminate\Console\Command;

class ElasticSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hero-search:elasticsearch:create {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating Elastic Search Index';

    /**
     * Client for elsatic search
     * 
     * @var string
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = app('elasticsearch');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!class_exists($model = $this->argument('model'))) {
            return $this->error("{$model} could not be resolved");
        }

        $model = new $model;
        
        try {
            $this->client->indices()->create([
                'index' => $model->searchableAs(),
                'body'  => [
                    'settings' => [
                        'index' => [
                            'analysis'  => [
                                'filter' => $this->filters(),
                                'analyzer' => $this->analyzer()
                            ]
                        ]
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    /**
     * Getting filters for index
     *
     * @return array
     */
    protected function filters()
    {
        return [
            'words_splitter' => [
                'catenate_all'      => 'true',
                'type'              => 'word_delimiter',
                'preserve_original' => 'true'
            ]
        ];
    }

    /**
     * Getting analyzer
     *
     * @return array
     */
    protected function analyzer()
    {
        return [
            'default' => [
                'filter'       => ['lowercase', 'words_splitter'],
                'char_filter'  => ['html_strip'],
                'type'         => 'custom',
                'tokenizer'    => 'standard' 
            ]
        ];
    }
}
