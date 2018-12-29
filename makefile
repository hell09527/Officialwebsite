deploy-staging:
	rsync -n -avz -e 'ssh -p 10022' --delete -C --exclude-from .rsyncignore .  deploy@h19.ushopal.com:/home/wwwroot/h19.ushopal.com
deploy-staging-go:
	rsync  -avz -e 'ssh -p 10022' --delete -C --exclude-from .rsyncignore .  deploy@h19.ushopal.com:/home/wwwroot/h19.ushopal.com

deploy-prod:
	rsync -n -avz -e 'ssh -p 10022' --delete -C --exclude-from .rsyncignore .  deploy@h19.ushopal.com:/home/wwwroot/www.ushopal.com
deploy-prod-go:
	rsync  -avz -e 'ssh -p 10022' --delete -C --exclude-from .rsyncignore .  deploy@h19.ushopal.com:/home/wwwroot/www.ushopal.com


