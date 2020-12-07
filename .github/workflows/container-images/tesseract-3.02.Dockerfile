FROM docker.io/library/fedora:20

RUN yum install -y curl git php-cli php-pecl-xdebug unzip \
	tesseract-3.02.02-3.fc20 \
	tesseract-langpack-deu \
	tesseract-langpack-jpn \
	tesseract-langpack-spa &&\
	yum clean all && rm -rf /var/cache/yum && rm -rf /var/tmp/yum-*

RUN curl -sko- https://getcomposer.org/installer |\
	php -- --quiet --filename=composer --install-dir=/usr/local/bin

ENTRYPOINT ["/bin/bash"]
