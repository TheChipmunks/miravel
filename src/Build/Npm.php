<?php

namespace Miravel\Buid;

//npm run production -- --env.mixfile=storage/miravel/test-child/webpack.mix.js

class Npm {
    
    protected $RootDir = null;
    
    protected $WebPackFile = null;
    
    public function __construct($RootDir, $WebPackFile) {
        $this->RootDir = $RootDir;
        $this->WebPackFile = $WebPackFile;
        
        if(!file_exists($RootDir . $WebPackFile)){
            throw new \Exception("File Not Found: [{$WebPackFile}]");
        }
    }
    
    public function execute(){
        $output = "";
        exec("which npm", $output);
        
        if(!$output){
           throw new \Exception("NPM Not Found!");
        }
        
        if($output){
            exec("cd {$this->RootDir}; npm run production -- --env.mixfile={$this->WebPackFile}", $output);
        }
        
        return $output;
    }
    
}