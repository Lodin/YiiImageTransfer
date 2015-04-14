ImageLoader = function (imgID, fileID, maxsize)
{
    if (maxsize === undefined)
        throw 'Parameter `maxsize` is not defined';
    
    if (maxsize.w === undefined || maxsize.h === undefined || maxsize.length > 2)
        throw 'Parameter `maxsize` should have signature {w:0; h:0}';
    
    jQuery('#' + fileID).change (function(){
        if (this.files && this.files[0])
        {
            var reader = new FileReader();

            reader.onload = function (e) {
                var img = new Image;
                
                img.src = e.target.result;
                
                img.onload = function(){
                    if (this.width > maxsize.w)
                    {
                        var proportion = maxsize.w / this.width;
                        this.width = maxsize.w;
                        this.height *= proportion;
                    }
                    
                    if (this.height > maxsize.h)
                    {
                        var proportion = maxsize.h / this.height;
                        this.height = maxsize.h;
                        this.width *= proportion;
                    }
                    
                    var $img = jQuery('#' + imgID);
                    
                    $img.attr('src', this.src);
                    $img.attr('width', this.width);
                    $img.attr('height', this.height);
                };
            };
            
            reader.readAsDataURL (this.files[0]);
        }
    });
};