var ImageUploader = (function(){
    function ImageUploader(options)
    {
        this.maxWidth = options.maxWidth;
        this.maxHeight = options.maxHeight;
    }
    
    ImageUploader.prototype.setFieldData = function(data)
    {
        this.newField = data;
    };
    
    ImageUploader.prototype.handleMultiplication = function()
    {
        var _this = this;
        
        var buttons = document.getElementsByClassName('imgtrup-btn-multiplicate');
        if(buttons.length > 0) {
            if(this.newField === undefined) {
                throw new Error('The `setFieldData` method should be called');
            } 
            
            var wrappers = document.getElementsByClassName('imgtrup-wrapper');
            
            for(var i = 0; i < buttons.length; i++) {
                var ul = wrappers[i].getElementsByTagName('ul');
            
                buttons[i].addEventListener('click', function(e){
                    e.preventDefault();
                    
                    var li = document.createElement('li');
                    
                    for(var property in _this.newField.wrapper) {
                        li[property] = _this.newField.wrapper[property];
                    }
                    
                    var img = new Image;
                    for(var property in _this.newField.img) {
                        img[property] = _this.newField.img[property];
                    }
                    
                    var input = document.createElement('input');
                    input.type = 'file';
                    input.classList.add('imgtrup-input');
                    input.name = _this.newField.input.name;
                    
                    li.appendChild(img);
                    li.appendChild(input);
                    ul.appendChild(li);
                    
                    input.addEventListener('change', function(){
                        _this.uploadHandler(this, img);
                    });
                });
            }
        }
    };
    
    ImageUploader.prototype.handleUploading = function()
    {
        var _this = this;
        
        var wrappers = document.getElementsByClassName('imgtrup-wrapper');
        
        for(var i = 0; i < wrappers.length; i++) {
            var images = wrappers[i].getElementsByTagName('img');
            var fileinputs = wrappers[i].getElementsByClassName('imgtrup-input');
            
            for(var j = 0; j < fileinputs.length; j++) {
                fileinputs[j].addEventListener('change', function(){
                    _this.uploadHandler(this, images[j]);
                });
            }
        }
    };
    
    ImageUploader.prototype.uploadHandler = function(fileinput, image)
    {
        var _this = this;
        
        if (fileinput.files && fileinput.files[0])
        {
            var reader = new FileReader();

            reader.onload = function (e) {
                var img = new Image;
                
                img.src = e.target.result;
                
                img.onload = function(){
                    if (this.width > _this.maxWidth)
                    {
                        var proportion = _this.maxWidth / this.width;
                        this.width = _this.maxWidth;
                        this.height *= proportion;
                    }
                    
                    if (this.height > _this.maxHeight)
                    {
                        var proportion = _this.maxHeight / this.height;
                        this.height = _this.maxHeight;
                        this.width *= proportion;
                    }
                    
                    image.attr('src', this.src);
                    image.attr('width', this.width);
                    image.attr('height', this.height);
                };
            };
            
            reader.readAsDataURL(fileinput.files[0]);
        }
    };
    
    return ImageUploader;
})();