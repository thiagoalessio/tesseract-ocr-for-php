FROM base/archlinux:2018.01.01

RUN pacman -Sy --noconfirm curl git php xdebug && \
	pacman -U  --noconfirm https://archive.archlinux.org/packages/t/tesseract/tesseract-3.05.01-3-x86_64.pkg.tar.xz && \
	pacman -U  --noconfirm https://archive.archlinux.org/packages/t/tesseract-data-eng/tesseract-data-eng-3.02.02-5-any.pkg.tar.xz && \
	pacman -U  --noconfirm https://archive.archlinux.org/packages/t/tesseract-data-deu/tesseract-data-deu-3.02.02-5-any.pkg.tar.xz && \
	pacman -U  --noconfirm https://archive.archlinux.org/packages/t/tesseract-data-jpn/tesseract-data-jpn-3.02.02-5-any.pkg.tar.xz && \
	pacman -U  --noconfirm https://archive.archlinux.org/packages/t/tesseract-data-spa/tesseract-data-spa-3.02.02-5-any.pkg.tar.xz

RUN echo zend_extension=xdebug.so > /etc/php/conf.d/xdebug.ini

RUN curl -sko- https://getcomposer.org/installer | \
	php -- --quiet --filename=composer --install-dir=/usr/local/bin
