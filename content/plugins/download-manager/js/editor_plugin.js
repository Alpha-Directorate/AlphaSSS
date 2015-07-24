 
(function() {

    tinymce.create('tinymce.plugins.wpdm_tinyplugin', {

        init : function(ed, url){            
          
            ed.addCommand('mcedonwloadmanager', function() {
                                ed.windowManager.open({
                                        title: 'Download Manager',
                                        file : 'admin.php?wpdm_action=wpdm_tinymce_button',
                                        height: 500,
                                        width:530,                                        
                                        inline : 1
                                }, {
                                        plugin_url : url, // Plugin absolute URL
                                        some_custom_arg : 'custom arg' // Custom argument
                                });
                        });
            
            ed.addButton('wpdm_tinyplugin', {
                title : 'Download Manager: Insert Package or Category',
                cmd : 'mcedonwloadmanager',
                image: url + "/img/donwloadmanager.png"
            });
            
            
        },

        getInfo : function() {
            return {
                longname : 'WPDM - TinyMCE Button Add-on',
                author : 'Shaon',
                authorurl : 'http://www.wpdownloadmanager.com',
                infourl : 'http://www.wpdownloadmanager.com',
                version : "1.0"
            };
        }

    });

    tinymce.PluginManager.add('wpdm_tinyplugin', tinymce.plugins.wpdm_tinyplugin);
    
})();
