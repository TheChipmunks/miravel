<?php

namespace Miravel\Buid;

use  Miravel\Buid\Resources\Css;
use  Miravel\Buid\Resources\Js;


class Mix {
    
    protected $Resources = null;
    protected $Dist = null;
    
    public function __construct(array $Resources, string $Dist) {
        $this->Resources = $Resources;
        $this->Dist = $Dist;
    }
    
    protected function checkDir(){
        if(!is_dir($this->Dist)){
            mkdir($this->Dist, '0755', true);
        }
    }
    
    protected function css() {
        $CssData = "";
        
        
        foreach ($this->Resources as $Resurs){
            
            $Css = ['css' => [], 'sass' => [], 'less' => []];
            
            if($Resurs instanceof Css){
                foreach ($Resurs->getFiles() as $File){
                    if($File->getExtension() == 'scss'){
                        $Css['sass'][] = "'" . $File . "'";
                    } else if($File->getExtension() == 'less'){
                        $Css['less'][] = "'" . $File . "'";
                    } else {
                        $Css['css'][] = "'" . $File . "'";
                    }
                }
            }
            
            $isPlainCss = !empty($Css['css']);
            $getPathInfo = pathinfo($Resurs->getDist());
            $distFileName = $getPathInfo['basename'];
            $distDir = $getPathInfo['dirname'];
            
            if(!empty($Css['sass'])){
                $Number = 1;
                foreach ($Css['sass'] as $Sass){
                    $SassFile = ($distDir . DIRECTORY_SEPARATOR . $distFileName . '_' . $Number . '.sass');
                    $Number++;
                    if($isPlainCss){
                        $Css['css'][] = "'" . $SassFile . "'";
                    }
                    $CssData .= <<<MIX_CSS
                    .sass({$Sass},
                    '{$SassFile}')
MIX_CSS;
                }
            }
                        
            if(!empty($Css['less'])){
                $Less = implode(",\n", $Css['less']);
                $LessFile = ($distDir . DIRECTORY_SEPARATOR . $distFileName . ($isPlainCss ? '.less' : ''));
                if($isPlainCss){
                    $Css['css'][] = "'" . $LessFile . "'";
                }
                $CssData .= <<<MIX_CSS
                    .less([
                        {$Less}
                    ],
                    '{$LessFile}')
MIX_CSS;
            }
                        
            if(!empty($Css['css'])){
                $PlainCss = implode(",\n", $Css['css']);
                $CssData .= <<<MIX_CSS
                    .combine([
                        {$PlainCss}
                    ],
                    '{$Resurs->getDist()}')
MIX_CSS;
            }
           
        }
        
        return $CssData;
    }
    
    protected function js() {
        $Js = "";
        foreach ($this->Resources as $Resurs){
            $Files = implode(",\n", array_map(function($item){
                return "'" . $item . "'";
            }, $Resurs->getFiles()));
                
                if($Resurs instanceof Js){
                    $Js .= <<<MIX_JS
                    .scripts([
                        {$Files}
                    ],
                    '{$Resurs->getDist()}')
MIX_JS;
                } 
        }
        
        return $Js;
    }
    
   
    protected function data() {
        $Js = $this->js();
        $Css = $this->css();
        
        return <<<JS
            let mix = require('laravel-mix');
            
            mix
            {$Js}
            {$Css}
            
JS;
    }
    
    public function generate(){
        $this->checkDir();
        
        file_put_contents($this->Dist . 'webpack.mix.js', $this->data());
        
        return $this->Dist . 'webpack.mix.js';
    }
        
    
    
}