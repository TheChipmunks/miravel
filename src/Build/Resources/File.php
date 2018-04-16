<?php

namespace Miravel\Buid\Resources;

class File {
    
    protected $File = null;
    protected $Extension = null;
    
    public function __construct(string $File) {
        $this->File = $File;
        
        if(!file_exists($File)){
            throw new \Exception("File Not Found: [{$File}]");
        }
        
        $Info = pathinfo($File);
        $this->Extension = $Info['extension'];
    }
    
    public function getExtension(){
        return $this->Extension;
    }
    
    public function __toString() {
        return $this->File;
    }
    
}
