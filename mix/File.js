let MixFile = require(LaravelMixPath + '/src/File');

class File extends MixFile {
	
    constructor(filePath) {
    		if(
    				filePath.indexOf('node_modules') == -1
    				&& filePath.indexOf('storage') == -1
    				){
    			filePath = ThemePath + filePath;
    		}
    		
    		super(filePath)
    }

}


module.exports = File;
