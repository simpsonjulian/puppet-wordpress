class wordpress::installation { 
  $www_user = 'www-data'
  $www_group = 'www-data'
  $wordpress_dir = '/data/wordpress'
  
  package { 
    'php5-mysql': ensure => present; 
    'php5-cli':  ensure => present; 
    'libapache2-mod-php5': ensure => present; 
    'php5': ensure => present; 
    'php5-cgi': ensure => present; 
    'libphp-phpmailer':   
      ensure => '1.73-4';
    'php5-gd':  ensure => present;  
             
    
    }
  
  file { "wordpress install":
    # make it owned by root 
    path => $wordpress_dir,
    mode => 755, 
    owner => root,
    group => root,
    source => "puppet:///wordpress/data/wordpress",
	  recurse => 'inf';
	  
  "upload dir":
    path => "${wordpress_dir}/wp-content/uploads",
		mode => 0755,
		owner => $www_user,
		group => $www_group,
		recurse => 'inf',
		require => File["wordpress install"];
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
 		
	exec {
		"/usr/sbin/a2enmod deflate": 
		unless => "test -f /etc/apache2/mods-enabled/deflate.load",
		require => Package["apache2"];
	}
}
