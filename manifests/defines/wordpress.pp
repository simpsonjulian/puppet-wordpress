define wordpress::blog ($name, $user, $fq_host, $password) {
  
  file {
    "wordpress config ${fq_host}":
      path => "/etc/wordpress/config-${fq_host}.php",
      ensure => file,
      content => template("wordpress/config-fqdn.php.erb"),
      require => File["wordpress etc dir"],
      require => Class["wordpress::installation"];
  }
  
}
