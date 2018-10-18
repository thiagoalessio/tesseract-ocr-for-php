FROM debian:wheezy

RUN apt-get -y update && \
	apt-get -y install curl git-core php5-cli=5.4.45-0+deb7u14 \
	--no-install-recommends &&\
	apt-get clean &&\
	rm -rf /var/lib/apt/lists/*

RUN curl -sko- https://getcomposer.org/installer | \
	php -- --quiet --filename=composer --install-dir=/usr/local/bin
