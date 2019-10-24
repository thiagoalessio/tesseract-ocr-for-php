FROM debian:wheezy

RUN apt-get -y update && \
	apt-get -y install curl git-core unzip php5-cli php5-xdebug php5-curl \
	tesseract-ocr=3.02.01-6 tesseract-ocr-deu tesseract-ocr-jpn tesseract-ocr-spa \
	--no-install-recommends &&\
	apt-get clean &&\
	rm -rf /var/lib/apt/lists/*

RUN curl -sko- https://getcomposer.org/installer | \
	php -- --quiet --filename=composer --install-dir=/usr/local/bin
