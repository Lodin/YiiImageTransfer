Uploader = function (max, maxsize)
{
    if (max === undefined)
        throw 'Parameter `max` is not defined';
    
    if (maxsize === undefined)
        throw 'Parameter `maxsize` is not defined';
    
    if (maxsize.w === undefined || maxsize.h === undefined || maxsize.length > 2)
        throw 'Parameter `maxsize` should have signature {w:0; h:0}';
    
    jQuery('.imageloader-uploader')
            .find('.imageloader-photolist')
            .find('.imageloader-image').each (function(i, element){
                var imgID = jQuery(element).find('img').attr('id');
                ImageLoader (imgID, imgID + '-upload', maxsize);
            });
    
    jQuery('#imageloader-addbutton').click (function(){
        var $photolist = jQuery(this).closest('.imageloader-uploader').find('.imageloader-photolist');
      
        if ($photolist.find('.imageloader-image').length === max)
            return false;
        
        var imageData = JSON.parse ($photolist.find('input[type=hidden]').val());
        var imgID = imageData.id + $photolist.find ('.imageloader-image').length;

        $photolist.append (
            '<div' + imageData.wrapperOptions + ' width>'
            + '<img src="' + imageData.placeholder + '"'
            + 'id="'+ imgID + '"' 
            + 'width="' + imageData.width + 'px"' 
            + 'height="' + imageData.height + 'px" />'
            
            + '<input type="file" id="' + imgID + '-upload"' 
            + 'class="imageloader-upload"'
            + 'name="' + imageData.name + '" />'
            
            + '</div>'
        );

        ImageLoader (imgID, imgID + '-upload', maxsize);
    });
};