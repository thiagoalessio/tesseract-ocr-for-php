FROM debian:jessie
MAINTAINER thiagoalessio <thiagoalessio@me.com>

RUN apt-get -y update && \
	apt-get -y install curl git-core unzip php5-cli php5-xdebug php5-curl \
	tesseract-ocr=3.03.03-1 tesseract-ocr-deu tesseract-ocr-jpn tesseract-ocr-spa \
	--no-install-recommends

RUN curl -sko- https://getcomposer.org/installer | \
	php -- --quiet --filename=composer --install-dir=/usr/local/bin
