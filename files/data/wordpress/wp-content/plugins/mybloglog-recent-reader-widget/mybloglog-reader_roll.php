<?php

/*
Plugin Name: MyBlogLog Recent Readers Widget
Plugin URI: http://www.mybloglog.com/buzz/community/com_widget_wp.php
Description: Adds MyBlogLog Recent Readers widget to your blog.
Author: MyBlogLog Team
Version: 3.0.1
Author URI: http://www.mybloglog.com
*/

//URL to hit at mybloglog
$mbl_url = "http://www.mybloglog.com/buzz/plugins/community.php";

// This gets called at the plugins_loaded action
function widget_mybloglog_readerroll_init() {

	// Check for the required API functions
	if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control'))
		return;

	// This saves options and prints the widget's config form.
	function widget_mybloglog_control() {

		$options = $newoptions = get_option('widget_mybloglog');

		if ($_POST['mybloglog_rr-submit']) {
			$valid_code = urldecode($_REQUEST['mybloglog_rr-code']);
			// Making sure it's a MyBlogLog widget
			$n_m = preg_match('/^<(script|iframe|object|a)(.+)(src|value|href)(.+)(mybloglog\.com\/)(.+)mblID=([0-9]+)(.+)<\/(script|iframe|object|a)>$/im', $valid_code);
			if ($n_m == 0) {
				        $valid_code = '';
			}
			$newoptions['mybloglog_rr-code'] = $valid_code;

			$valid_code = urldecode($_REQUEST['mybloglog_rr-code_box']);
			// Making sure it's a MyBlogLog widget
			$n_m = preg_match('/^<(script|iframe|object|a)(.+)(src|value|href)(.+)(mybloglog\.com\/)(.+)mblID=([0-9]+)(.+)<\/(script|iframe|object|a)>$/im', $valid_code);
			if ($n_m == 0) {
				        $valid_code = '';
			}
			$newoptions['mybloglog_rr-code_box'] = $valid_code;

			$newoptions['mybloglog_rr-community_name'] = $_POST['mybloglog_rr-community_name'];
			$newoptions['mybloglog_rr-title'] = $_POST['mybloglog_rr-title'];
			$newoptions['mybloglog_rr-site_id'] = $_POST['mybloglog_rr-site_id'];
			$newoptions['mybloglog_rr-widget_type'] = $_POST['mybloglog_rr-widget_type'];
			$newoptions['mybloglog_rr-widget_color'] = $_POST['mybloglog_rr-widget_color'];
			$newoptions['mybloglog_rr-widget_images'] = $_POST['mybloglog_rr-widget_images'];
			$newoptions['mybloglog_rr-widget_orientation'] = $_POST['mybloglog_rr-widget_orientation'];
			$newoptions['mybloglog_rr-widget_width'] = $_POST['mybloglog_rr-widget_width'];
			$newoptions['mybloglog_rr-widget_rows'] = $_POST['mybloglog_rr-widget_rows'];
			$newoptions['mybloglog_rr-view_id'] = $_POST['mybloglog_rr-view_id'];
		}
		if ($options != $newoptions && $newoptions['mybloglog_rr-code']) {
			$options = $newoptions;
			update_option('widget_mybloglog', $options);
		}

		$widget_type = $options['mybloglog_rr-widget_type'];
		$widget_color = $options['mybloglog_rr-widget_color'];
		$widget_images = $options['mybloglog_rr-widget_images'];
		$widget_orientation = $options['mybloglog_rr-widget_orientation'];
		$widget_width = $options['mybloglog_rr-widget_width'];
		$widget_rows = $options['mybloglog_rr-widget_rows'];
		$widget_title = $options['mybloglog_rr-title'];


		if (!$widget_rows)
			$widget_rows = "4";

		if (!$widget_width)
			$widget_width = "220px";

		$select_widget_type[$widget_type] = "selected = 'selected'";
		$select_widget_color[$widget_color] = "selected = 'selected'";
		$select_widget_images[$widget_images] = "selected = 'selected'";
		$select_widget_orientation[$widget_orientation] = "selected = 'selected'";
?>
				<div>
				  <p style="text-align:left">
				  Let the world know who's visiting your blog and learn more about your readers as well. With this widget you'll get a public photo lineup of who's been reading your blog and, after claiming your blog on <a href='http://www.mybloglog.com'>mybloglog.com</a> a private stats page showing recent activity.
				  </p>
<script type="text/javascript">
function mbl_e(id){
    if(document.getElementById != null){
        return document.getElementById(id);
    }
    
    if(document.all != null){
        return document.all[id];
    }

    if(document.layers != null){
        return document.layers[id];
    }
    return null;
}


function mbl_load_plugin(cname, code,site_id) {
    mbl_e("mybloglog_rr-code").value = code;
    mbl_e('mybloglog_rr-community_name').value = cname;
    mbl_e('mybloglog_rr-site_id').value = site_id;
}
   	
    function widget_update_type(flag) {
    	
        var color = document.getElementById('widget_color');
        color = color.options[color.selectedIndex].value;
    
        var images = document.getElementById('widget_images');
        images = images.options[images.selectedIndex].value;
        
        var type = document.getElementById('widget_type');
        type = type.options[type.selectedIndex].value;

        var orientation = document.getElementById('widget_orientation');
        orientation = orientation.options[orientation.selectedIndex].value;

        var width = parseInt(document.getElementById('widget_width').value);
        var rows = parseInt(document.getElementById('widget_rows').value);
        
        if (width < 150) {
            width = 150;
            alert('Minimum allowed width is 150px.');
        }
        
        if (width > 1200) {
            width = 1200;
            alert('Maximum allowed width is 1200px.');
        }

        if (rows < 0) rows = 1;
        if (rows.length == 0) rows = 4;
        
        document.getElementById('widget_rows').value = rows;
        document.getElementById('widget_width').value = width + 'px';
       
        document.getElementById('mybloglog_rr-code').value = escape('<script src=\"http://pub.mybloglog.com/comm3.php?mblID='+mbl_e('mybloglog_rr-site_id').value+'&amp;r=widget&amp;is='+images+'&amp;o='+orientation+'&amp;ro='+rows+'&amp;cs='+color+'&amp;ww='+width+'&amp;wc='+type+'\"><\/script>');
        if(flag==2 && document.getElementById('mybloglog_rr-code_box').value)
            document.getElementById('mybloglog_rr-code').value =escape(document.getElementById('mybloglog_rr-code_box').value);
        
    }
    function toggleView(view)
    {
        mbl_e('mybloglog_rr-view_id').value=view;
    	if(view==2)
    	{
    		mbl_e('mybloglog_rr-simple').style.display='none';
    		mbl_e('mybloglog_rr-advanced').style.display='inline';
    		mbl_e('mybloglog_rr-title-view').style.display='inline';
    		
    	}
    	else
    	{
    		mbl_e('mybloglog_rr-advanced').style.display='none';
    		mbl_e('mybloglog_rr-simple').style.display='inline';
    		mbl_e('mybloglog_rr-title-view').style.display='inline';
	
	        <?php

		if (!$options['mybloglog_rr-code'] || !is_long($options['mybloglog_rr-site_id'])) {
?>     

  var thescript = document.createElement('script');
  thescript.setAttribute('type','text/javascript');
  thescript.setAttribute('src','<?php global $mbl_url; echo $mbl_url.'?url=';bloginfo('url');?>');
  document.getElementsByTagName('head')[0].appendChild(thescript);
           <?php

		}
?>		
    
    	}
    }
    
</script>
<style type='text/css'>
    div.es_lbl { float: left; width: 90px; text-align: right; padding-right: 5px; padding-top: 3px; }
    div.es_dta { float: left; padding-left: 10px; }
    div.es_row { clear: both; padding: 10px 0 5px 0;height:24px; }
    div.es_clear { clear: both; height: 25px; }
    select { width: 125px; } 
</style>
<div style='clear: both; margin-top: 0px;'></div>
         	
    	    <input type='hidden' name='mybloglog_rr-community_name' id='mybloglog_rr-community_name' value="<?php echo $options['mybloglog_rr-community_name'];?>" />
       	    <input type='hidden' name='mybloglog_rr-site_id' id='mybloglog_rr-site_id' value="<?php echo $options['mybloglog_rr-site_id'];?>" />
         	<input id="mybloglog_rr-code" name="mybloglog_rr-code" type='hidden' value='<?php echo urlencode((($options['mybloglog_rr-code']))); ?>'/>
            <input type='hidden' name='mybloglog_rr-view_id' id='mybloglog_rr-view_id' value="<?php echo $options['mybloglog_rr-view_id'];?>" />

        <div style="margin: 0pt; padding: 15px 0pt 0pt 5px; clear: both;">
            <p>Have code from mybloglog.com?
            <a onclick="toggleView(2);" href='javascript:void(0)'>Yes</a>
            <a onclick="toggleView(1);" href='javascript:void(0)'>No</a>
            </p>
            
        </div>
        
        <div style="display:none;" id='mybloglog_rr-title-view' >
            <b>Title:</b> <input name="mybloglog_rr-title" type="text"  value="<?php echo $widget_title?>" style="width:190px;" id="widget_title"/>
        <br>

        </div>  
        
    <div style="display:none;" id='mybloglog_rr-simple' >
        <br>

       <b>Change look and feel:</b>

        <div class="es_row">
            <div class="es_lbl">Style:</div>
            <div class="es_dta"><select name="mybloglog_rr-widget_type" onchange="javascript:widget_update_type();" id="widget_type"><option <?php echo $select_widget_type['single'];?> value="single" >One Column</option><option <?php echo $select_widget_type['multiple'];?> value="multiple">Multiple Columns</option></select></div>
        </div>

        <div class="es_row">
            <div class="es_lbl">Color:</div>
            <div class="es_dta">
                <select name="mybloglog_rr-widget_color" onchange="javascript:widget_update_type();" id="widget_color">
                    <option <?php echo $select_widget_color['black'];?> value="black">Black</option>
                    <option <?php echo $select_widget_color['blue'];?> value="blue"  >Blue</option>
                    <option <?php echo $select_widget_color['brown'];?> value="brown">Brown</option>
                    <option <?php echo $select_widget_color['green'];?> value="green">Green</option>
                    <option <?php echo $select_widget_color['orange'];?> value="orange">Orange</option>
                    <option <?php echo $select_widget_color['purple'];?> value="purple">Purple</option>
                    <option <?php echo $select_widget_color['red'];?> value="red">Red</option>
                </select>
            </div>
        </div>

        <div class="es_row">
            <div class="es_lbl">Avatar Size:</div>
            <div class="es_dta"><select  name="mybloglog_rr-widget_images" onchange="javascript:widget_update_type();" id="widget_images"><option <?php echo $select_widget_images['normal'];?> value="normal">Normal</option><option <?php echo $select_widget_images['small'];?> value="small">Small</option></select></div>
        </div>

        <div class="es_row">
            <div class="es_lbl">Flyout:</div>
            <div class="es_dta"><select name="mybloglog_rr-widget_orientation" onchange="javascript:widget_update_type();" id="widget_orientation"><option <?php echo $select_widget_orientation['l'];?> value="l">Left</option><option <?php echo $select_widget_orientation['r'];?> value="r">Right</option><option value="n">None</option></select></div>
        </div>

        <div style="padding: 10px 0pt 0pt 0px; clear: both;">
            <b>Modify width and height:</b>
        </div>        

        <div class="es_row">
            <div class="es_lbl">Width (<span style="font-style: italic;">150 - 1200</span>):</div>
            <div class="es_dta"><input name="mybloglog_rr-widget_width" type="text" onchange="javascript:widget_update_type();" style="width: 50px;" value="<?php echo $widget_width?>" id="widget_width"/></div>
        </div>
        <div class="es_row">
            <div class="es_lbl">Rows (<span style="font-style: italic;">1 - 100</span>):</div>
            <div class="es_dta"><input name="mybloglog_rr-widget_rows" type="text" onchange="javascript:widget_update_type(1);" style="width: 50px;" value="<?php echo $widget_rows?>" id="widget_rows"/></div>
        </div>  
          <div id='mybloglog_rr-script-code'>&nbsp;</div>        

    </div>
    <div id='mybloglog_rr-advanced' style='display:none;'>
            <br>
    
           <p><b>Copy paste your code here:</b></p>
           <textarea id="mybloglog_rr-code_box" onchange="javascript:widget_update_type(2);" name="mybloglog_rr-code_box" style="width:220px;height:110px;padding:0px;margin:0px;"><?php echo stripslashes(wp_specialchars($options['mybloglog_rr-code_box'], true)); ?></textarea>
    </div>            	

    <div style="height: 10px; clear: both;">&nbsp;</div>


      	

        	
				  <input type="hidden" name="mybloglog_rr-submit" id="mybloglog_rr-submit" value="1" />
				</div>
				


<?php

		if ($options['mybloglog_rr-view_id']) {
?> <script type='text/javascript'>
toggleView(<?php echo $options['mybloglog_rr-view_id'];?>);
</script>
<?php

		}

	}
	// This prints the widget
	function widget_mybloglog($args) {
		extract($args);
		global $mbl_url;
		$defaults = array ();
		$options = (array) get_option('widget_mybloglog');

		foreach ($defaults as $key => $value)
			if (!isset ($options[$key]))
				$options[$key] = $defaults[$key];
?>
		<?php echo $before_widget; ?>
			<?php
			    if($options["mybloglog_rr-title"])
			     echo $before_title . $options["mybloglog_rr-title"] . $after_title; //Edit if you want to add title ?>
			<?php


		if (trim($options['mybloglog_rr-code'])) {
			echo trim(stripslashes($options['mybloglog_rr-code']));
		} else {
?>
                <script type="text/javascript">

                function mbl_load_plugin(cname, code,site_id) {
                    document.write(code);
                }
                
                </script>
			    <script type="text/javascript" src='<?php echo $mbl_url."?gm=1&url=";bloginfo('url');?>'>
                </script>
			 
				<?php


		}
?>
		<?php echo $after_widget; ?>
<?php


	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget('MyBlogLog Reader Roll', 'widget_mybloglog');
	register_widget_control('MyBlogLog Reader Roll', 'widget_mybloglog_control', 240, 300);

}

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_mybloglog_readerroll_init');
?>
