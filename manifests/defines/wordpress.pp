define wordpress::blog ($name, $user, $fq_host, $password) {
  include wordpress::installation
  file {
    "wordpress etc dir":
      path => "/etc/wordpress",
      ensure => directory;
      
    "wordpress config":
      path => "/etc/wordpress/config-${fq_host}.php",
      ensure => file,
      content => template("wordpress/config-fqdn.php.erb"),
      require => File["wordpress etc dir"];
  }
  
}