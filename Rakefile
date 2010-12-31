opts="-rcvpe ssh --exclude=.git "
source="files/data/wordpress"
host_path="cleo:/data"

task :deploy do
  sh "rsync #{opts} #{source} #{host_path}"
end

task :diff do 
  sh "rsync --dry-run -i --delete --delete-excluded #{opts} #{source} #{host_path}"
  sh "rsync --dry-run -i #{opts} #{source} #{host_path}"
end
