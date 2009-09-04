define wordpress::blog ($fq_host, $password) {
  include wordpress::installation
  file {
    "wordpress config":
      path => "/etc/wordpress/config-${fq_host}.php",
      ensure => file,
      content => template("wordpress/config-fqdn.pp");
  }
  
}