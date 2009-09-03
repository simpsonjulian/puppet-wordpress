class wordpress::installation { 
  $www_user = 'www-data'
  $www_group = 'www-data'
  $wordpress_dir = '/data/wordpress'
  
  package { 'php5-mysql': ensure => installed  }
  
  file { "wordpress install":
    # make it owned by root 
    path => $wordpress_dir,
    mode => 755, 
    owner => root,
    group => root,
    source => "puppet:///files/data/wordpress",
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

class wordpress::apache inherits wordpress::installation {
 		
	exec {
		"/usr/sbin/a2enmod deflate": 
		unless => "test -f /etc/apache2/mods-enabled/deflate.load",
		require => Package["apache2"];
	}
}
