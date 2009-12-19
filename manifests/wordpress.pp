class wordpress  {
  $www_user = 'www-data'
  $www_group = 'www-data'
  $wordpress_dir = '/data/wordpress'
  class installation { 
    
    package {   
      'php5-mysql': ensure => present; 
      'php5-cli':   ensure => present; 
      'libapache2-mod-php5': 
                    ensure => present; 
      'php5':       ensure => present; 
      'php5-cgi':   ensure => present; 
      'libphp-phpmailer': 
                    ensure => latest;
      'php5-gd':    ensure => present;  
               
      
      }
    
    exec { 
      "wordpress files":
        command => "/usr/bin/rsync -avp /etc/puppet/modules/wordpress/files/data/wordpress /data",
    }
  
    file {
      "content dir":
        path => "${wordpress_dir}/wp-content",
        ensure => directory;
        	  
    "upload dir":
      path => "${wordpress_dir}/wp-content/uploads",
      ensure => directory,
      mode => 0755,
      owner => $www_user,
      group => $www_group,
      require => File["content dir"];
  		
     "wordpress etc dir":
        path => "/etc/wordpress",
        ensure => directory;
    }
      
  }
  class supercache {
   file{"$wordpress_dir/wp-content/advanced-cache.php":
      owner => $www_user,
      group => $www_group,
      require => Class["wordpress::installation"];
    }
  }


  class sitemap {
    file {"$wordpress_dir/sitemap.xml":
  		owner => $www_user,
  		group => $www_group
  	}
  	
  	file {"$wordpress_dir/sitemap.xml.gz":
  		owner => $www_user,
  		group => $www_group
  	}
   		
        # deflate now comes from a module type

}
