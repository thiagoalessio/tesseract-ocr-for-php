FROM debian:stretch

RUN apt-get -y update && \
	apt-get -y install curl git-core unzip php-cli php-xdebug php-xml php-curl \
	tesseract-ocr=3.04.01-5 tesseract-ocr-deu tesseract-ocr-jpn tesseract-ocr-spa \
	--no-install-recommends &&\
	apt-get clean &&\
	rm -rf /var/lib/apt/lists/*

RUN curl -sko- https://getcomposer.org/installer | \
	php -- --quiet --filename=composer --install-dir=/usr/local/bin
