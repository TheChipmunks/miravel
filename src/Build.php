<?php

namespace Miravel;

use Miravel\Facade as MiravelFacade;
use Miravel\Exceptions\ThemeNotFoundException;

use  Miravel\Buid\Resources\Css;
use  Miravel\Buid\Resources\Js;
use  Miravel\Buid\Mix as MiravelMix;
use  Miravel\Utilities;

class Build {
    
    protected $Theme = null;
    protected $themeConfig = null;
    
    protected $Resurses = [];
    
    protected $Dist = null;
    
    
    public function __construct(string $themeName) {
        $this->Theme = MiravelFacade::makeTheme($themeName);
        
        if(!$this->Theme->exists()){
            throw new ThemeNotFoundException(['name' => $themeName]);
        }
            
        $this->themeConfig = $this->Theme->getConfig();
        $this->Dist = Utilities::getDistPath()  . DIRECTORY_SEPARATOR . $this->Theme->getName() . DIRECTORY_SEPARATOR;
        
        $this->init();
    }
    
    protected function init() {
        if(isset($this->themeConfig['build'])) {
            if(isset($this->themeConfig['build']['css'])) {
                $this->initCss($this->themeConfig['build']['css']);
            }
            if(isset($this->themeConfig['build']['js'])) {
                $this->initJs($this->themeConfig['build']['js']);
            }
        }
    }
    
    protected function scanDir($Dir, $Regex, $Depth = 100){
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Dir), \RecursiveIteratorIterator::LEAVES_ONLY);
        $objects->setMaxDepth($Depth);
        return new \RegexIterator($objects, $Regex, \RecursiveRegexIterator::GET_MATCH);
    }
    
    protected function initCss($Css) {
        foreach ($Css as $BundleName => $Data){
            $this->Resurses[$BundleName] = new Css($BundleName);
            foreach ($Data['src'] as $Files) {
                $Resource = $this->Theme->getResource($Files);
                
                if(is_dir($Resource->getPathname())){
                    foreach ($this->scanDir($Resource->getPathname(), '/^(.*.css|.*.less|.*.scss)$/i') as $FileName => $FileObject) {
                        $this->Resurses[$BundleName]->addFile($FileName);
                    }
                } else {
                    $this->Resurses[$BundleName]->addFile($Resource->getPathname());
                }
            }
            
            $this->Resurses[$BundleName]->setDist($this->Dist . $Data['dist']);
        }
    }
    
    protected function initJs($Js) {
        foreach ($Js as $BundleName => $Data){
            $this->Resurses[$BundleName] = new Js($BundleName);
            foreach ($Data['src'] as $Files) {
                $Resource = $this->Theme->getResource($Files);
                
                if(is_dir($Resource->getPathname())){
                    foreach ($this->scanDir($Resource->getPathname(), '/^(.*.js)$/i') as $FileName => $FileObject) {
                        $this->Resurses[$BundleName]->addFile($FileName);
                    }
                } else {
                    $this->Resurses[$BundleName]->addFile($Resource->getPathname());
                }
            }
            
            $this->Resurses[$BundleName]->setDist($this->Dist .  $Data['dist']);
        }
    }
    
    public function generate() {
        $Mix = new MiravelMix($this->Resurses, $this->Dist);
        echo $Mix->generate();
    }
    
}
