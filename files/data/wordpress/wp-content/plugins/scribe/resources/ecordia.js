function ecordia(ecordia_dependency){
    this.ecordiaDependency = ecordia_dependency;
    this.buttonClicked = false;
    this.errorThickbox = false;
    this.TB_WIDTH = 950;
    this.TB_HEIGHT = 600;
    this.original_TB_WIDTH = this.TB_WIDTH;
    this.original_TB_HEIGHT = this.TB_HEIGHT;
    this.elementMap = {
        'aioseo': {
            title: 'aiosp_title',
            description: 'aiosp_description'
        },
		'thesis': {
            title: 'thesis_title',
            description: 'thesis_description'
        },
        'hybrid': {
            title: 'Title',
            description: 'Description'
        },
		'headwa': {
            title: 'seo_title',
            description: 'seo_description'
        }
    };
    
    this.showError = function(message, extended){
        ecordia.errorThickbox = true;
        this.TB_HEIGHT = 300;
        this.TB_WIDTH = 300;
        tb_show('Scribe Content Optimizer', 'media-upload.php?tab=ecordia-error&message=' + encodeURIComponent(message) + '&extended=' + encodeURIComponent(extended) + '&TB_iframe=true', false);
    };
    
    this.checkElementComplete = function(val, elementId){
        var $element = jQuery('#' + elementId);
        if ('' == val) {
            $element.addClass('incomplete').removeClass('complete');
            return false;
        }
        else {
            $element.addClass('complete').removeClass('incomplete');
            return true;
        }
    };
    
    this.getElementValue = function(element){
        if (element.attr('id') == 'content') {
            if (typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
                var value = ed.getContent().replace('<br />','');
				return value;
            }
            else {
                return jQuery.trim(element.val());
            }
        }
        else {
            return jQuery.trim(element.val());
        }
    }
    
    this.completeListItems = function(){
        var ecordia = this;
        jQuery.each(this.elementIds, function(name, value){
            ecordia.checkElementComplete(ecordia.getElementValue(jQuery('#' + value)), 'ecordia-seo-analysis-requirement-' + name);
        });
    }
    
    this.toggleAjaxIndicator = function(){
        jQuery('#ecordia-ajax-feedback').toggleClass('ajax-feedback').toggle();
    }
    
    this.analyzing = false;
    
    this.analyzePost = function(event){
        event.preventDefault();
        if (ecordia.analyzeButtonEnabled() && !ecordia.analyzing) {
            ecordia.analyzing = true;
            ecordia.toggleAjaxIndicator();
            var post_ID = jQuery('#post_ID').val();
            if ('' == post_ID || post_ID < 1) {
                autosave();
            }
            else {
                ecordia.sendAnalysisRequest();
            }
            
        }
        else 
            if (!ecordia.analyzing) {
				ecordia.showError('Review Required','Missing Content, Title, or Description.');
            }
    };
    
    this.sendAnalysisRequest = function(){
        jQuery.post('admin-ajax.php', {
            'action': 'ecordia_analyze',
            'title': ecordia.getElementValue(jQuery('#' + ecordia.elementIds['title'])),
            'content': ecordia.getElementValue(jQuery('#' + ecordia.elementIds['content'])),
            'description': ecordia.getElementValue(jQuery('#' + ecordia.elementIds['description'])),
            'pid': jQuery('#post_ID').val()
        }, function(data){
            ecordia.analyzing = false;
            ecordia.toggleAjaxIndicator();
            if (data.success) {
                jQuery('#ecordia .inside').html(data.meta);
                ecordia.registerHandlers();
                jQuery('#ecordia-seo-analysis-review-button').click();
            }
            else {
                ecordia.showError(data.message, data.extended);
            }
        }, 'json');
    }
    
    this.showReview = function(event){
        event.preventDefault();
        if (typeof(tb_show) != 'undefined') {
            $this = jQuery(this);
            tb_show('Scribe Content Analysis', $this.attr('href'), false);
        }
    };
    
    this.enabled = function(){
        return typeof(this.ecordiaDependency) != 'undefined' && typeof(this.elementMap[this.ecordiaDependency]) != 'undefined';
    }
    
    this.analyzeButtonEnabled = function(){
        var shouldEnable = true;
        jQuery.each(this.elementIds, function(name, value){
            if (ecordia.getElementValue(jQuery('#' + value)) == '') {
                shouldEnable = false;
            }
            return shouldEnable;
        });
        return shouldEnable;
    }
    
    this.elementIds = {
        'title': '',
        'content': 'content',
        'description': ''
    }
    
    if (this.enabled()) {
        this.elementIds['title'] = this.elementMap[this.ecordiaDependency]['title'];
        this.elementIds['description'] = this.elementMap[this.ecordiaDependency]['description'];
    }
    
    this.registerHandlers = function(){
        var ecordia = this;
        
        if (jQuery('#aiosp').length > 0) {
            jQuery('input[name=' + this.elementMap['aioseo']['title'] + ']').attr('id', this.elementMap['aioseo']['title']);
            jQuery('textarea[name=' + this.elementMap['aioseo']['description'] + ']').attr('id', this.elementMap['aioseo']['description']);
        }
        
        
        jQuery('.ecordia-close-thickbox').click(function(event){
            event.preventDefault();
            top.tb_remove();
        });
        jQuery('#ecordia-setttings-page-from-thickbox').click(function(event){
            top.tb_remove();
        });
        jQuery('#ecordia-seo-analysis-analyze-button,#ecordia-seo-analysis-review-button').click(this.registerButtonClicked);
        jQuery('#ecordia-seo-analysis-analyze-button').click(this.analyzePost);
        jQuery('#ecordia-seo-analysis-review-button').click(this.showReview);
        jQuery.each(this.elementIds, function(name, id){
            jQuery('#' + id).blur(function(event){
                ecordia.blurEvent();
            }).blur();
        });
    }
    
    this.blurEvent = function(){
        ecordia.completeListItems();
        if (ecordia.analyzeButtonEnabled()) {
            jQuery('#ecordia-seo-analysis-analyze-button').removeClass('ecordia-disabled');
        }
        else {
            jQuery('#ecordia-seo-analysis-analyze-button').addClass('ecordia-disabled');
        }
    }
    
    this.registerButtonClicked = function(event){
        ecordia.buttonClicked = true;
    }
}

jQuery(document).ready(function(){
	jQuery('#ecordia-connection-method').change(function() {
		if(jQuery(this).val() == 'https') {
			jQuery('#ecordia-https-warning').css({display:'block'});
		} else {
			jQuery('#ecordia-https-warning').css({display:'none'});
		}
	}).change();
	
    if (typeof(ecordia) == 'object') {
        ecordia.registerHandlers();
        if (ecordia.enabled()) {
            if (typeof(tb_position) != 'undefined') {
                // CRAZY ThickBox positioning stuff so that the Thickbox isn't overridden by WordPress
                var ecordia_old_tb_position = tb_position;
                tb_position = function(){
                    if (ecordia.buttonClicked) {
                        var tbWindow = jQuery('#TB_window');
                        var W = ecordia.TB_WIDTH;
                        var H = ecordia.TB_HEIGHT;
                        
                        var fromTop = ((jQuery(window).height() - H) / 2);
                        if (tbWindow.size()) {
                            tbWindow.width(W - 50).height(H - 45);
                            tbWindow.css('marginTop', fromTop);
                            jQuery('#TB_iframeContent').width(W - 50).height(H - 75);
                            tbWindow.css({
                                'margin-left': '-' + parseInt(((W - 50) / 2), 10) + 'px'
                            });
                            if (typeof document.body.style.maxWidth != 'undefined') {
                                tbWindow.css({
                                    'top': '10px',
                                    'margin-top': fromTop
                                });
                            }
                            jQuery('#TB_title').css({
                                'background-color': '#222',
                                'color': '#cfcfcf'
                            });
                        };
                                            }
                    else {
                        ecordia_old_tb_position();
                    }
                };
                var ecordia_old_tb_remove = tb_remove;
                tb_remove = function(){
                    if (ecordia.buttonClicked) {
                        ecordia.buttonClicked = false;
                    }
                    if (ecordia.errorThickbox) {
                        ecordia.errorThickbox = false;
                        ecordia.TB_HEIGHT = ecordia.original_TB_HEIGHT;
                        ecordia.TB_WIDTH = ecordia.original_TB_WIDTH;
                    }
                    ecordia_old_tb_remove();
                };
            }
            if (typeof(autosave_saved_new) != 'undefined') {
                var ecordia_old_autosave_saved_new = autosave_saved_new;
                autosave_saved_new = function(response){
                    ecordia_old_autosave_saved_new(response);
                    if (ecordia.analyzing) {
                        ecordia.sendAnalysisRequest();
                    }
                }
            }
        }
    }
});
