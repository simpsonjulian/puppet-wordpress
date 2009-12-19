class wordpress  {
  $www_user = 'www-data'
  $www_group = 'www-data'
  $wordpress_dir = '/data/wordpress'
  notice("www-group is $www_group in the wordpress class")
  class installation { 
    include wordpress
  notice("www-group is $www_group in the wordpress::installation class")
    
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
        path => "${wordpress::wordpress_dir}/wp-content",
        ensure => directory;
        	  
      "upload dir":
        path => "${wordpress::wordpress_dir}/wp-content/uploads",
        ensure => directory,
        mode => 0755,
        owner => $wordpress::www_user,
        group => $wordpress::www_group,
        require => File["content dir"],
        require => Class["wordpress"];
  		
     "wordpress etc dir":
        path => "/etc/wordpress",
        ensure => directory;
    }
      
  }
  class supercache {
   file{"${wordpress::wordpress_dir}/wp-content/advanced-cache.php":
      owner => $wordpress::www_user,
      group => $wordpress::www_group,
      require => Class["wordpress"],
      require => Class["wordpress::installation"];
    }
  }


  class sitemap {
    file {"${wordpress::wordpress_dir}/sitemap.xml":
  		owner => $wordpress::www_user,
  		group => $wordpress::www_group,
                require => Class["wordpress"];
  	 }
  	
  	file {"${wordpress::wordpress_dir}/sitemap.xml.gz":
  		owner => $wordpress::www_user,
  		group => $wordpress::www_group,
                require => Class["wordpress"];
  	}
   		
        # deflate now comes from a module type

}}
