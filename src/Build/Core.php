<?php

namespace Miravel\Buid;

abstract class Core {
    
    protected $Name = null;
    
    protected $Files = [];
    
    protected $Dist = null;
    
    public function __construct(string $Name) {
        $this->setName($Name);
    }
    
    public function setName(string $Name){
        $this->Name = $Name;
    }
    
    public function getName() : string {
        return $this->Name;
    }
    
    public function setFiles(array $Files){
        $this->Files = $Files;
    }
    
    public function getFiles() : array{
        return $this->Files;
    }
    
    public function addFile(string $File) {
        $this->Files[] = $File;
    }
    
    public function setDist(string $Dist){
        $this->Dist = $Dist;
    }
    
    public function getDist() : string {
        return $this->Dist;
    }
    
}
