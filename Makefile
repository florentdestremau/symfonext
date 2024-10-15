start:
	symfony serve -d
	symfony local:run -d npm run dev

install:
	composer install
	npm install

stop:
	symfony server:stop
