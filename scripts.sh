# install js packages
docker run -it --rm -v "$PWD":/usr/src/app -w /usr/src/app node:10.16 yarn install

# install php packages
docker run --rm --interactive --tty --volume $PWD:/app composer install
