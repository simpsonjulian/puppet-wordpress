class wordpress::base { 
  $www_user = 'www-data'
  $www_group = 'www-data'
  $wordpress_dir = '/data/wordpress'
  
  package { 'php5-mysql': ensure => installed  }
  
  remotefile { "wordpress install":
    # make it owned by root 
    path => $wordpress_dir
    mode => 755, 
    owner => root,
    group => root,
    source => "/data/wordpress",
	  recurse => 'inf'
  }
    
}

define wordpress::sitemap {
  include wordpress::base
  file {"$wordpress_dir/sitemap.xml":
		owner => '${www_user}',
		group => '${www_group}'
	}
	
	file {"$wordpress_dir/sitemap.xml.gz":
		owner => '${www_user}',
		group => '${www_group}'
	}

}

class wordpress::apache inherits wordpress::base {
 	

	exec { "/usr/sbin/a2ensite www.build-doctor.com":
	  path => "/usr/bin:/usr/sbin:/bin",
	  require => File["wordpress"]
	            
	
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
