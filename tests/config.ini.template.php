; <?php exit; ?> DO NOT REMOVE THIS LINE
; this file is just here for documentation purpose
; the config.ini.php is normally created during the installation process
; when this file is absent it triggers the Installation process
; the config.ini.php file contains information about the super user and the database access

[superuser]
login=		@superuser.loging@
password=	@superuser.password@
email=		@superuser.email@

[database]
host=		@database.main.host@
port=		@database.main.port@
username=	@database.main.username@
password=	@database.main.password@
dbname=		@database.main.name@
adapter=	PDO_PGSQL
tables_prefix=	piwik_

[database_tests]
host=		@database.test.host@
port=		@database.test.port@
username=	@database.test.username@
password=	@database.test.password@
dbname=		@database.test.name@
adapter=	PDO_PGSQL
tables_prefix=	piwiktests_
