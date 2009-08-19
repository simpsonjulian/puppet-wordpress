class wordpress::base { 
  $www_user = 'www-data'
  $www_group = 'www-data'
  $wordpress_dir = '/data/wordpress'
  
  package { 'php5-mysql': ensure => installed  }
  
  remotefile { $wordpress_dir:
		mode => 755, 
    source => "/data/wordpress",
		recurse => 'inf'
  }
  
  file {"wordpress upload dir":
    path => "${wordpress_dir}/wp-content/uploads",
		mode => 0755,
		owner => '${www_user}',
		group => '${www_group}',
		recurse => 'inf'
	}
}

class wordpress::apache inherits wordpress::base {
 	

	exec { "/usr/sbin/a2ensite www.build-doctor.com":
	    	path => "/usr/bin:/usr/sbin:/bin",
	    	require => Package["wordpress"]
	            
	}
	
	file {"/usr/local/share/wordpress/sitemap.xml":
			owner => '${www_user}',
			group => '${www_group}'
	}
	
	file {"/usr/local/share/wordpress/sitemap.xml.gz":
			owner => '${www_user}',
			group => '${www_group}'
	}
	
	remotefile { "//wordpress":
		    mode => 755, 
		    source => "/etc/wordpress",
		    recurse => 'inf',
		    require => Package["wordpress"]
	}	
	
	exec {
		"/usr/sbin/a2enmod deflate": require => Package["apache2"];
	}
}
