FROM debian:stretch
MAINTAINER thiagoalessio <thiagoalessio@me.com>

RUN apt-get -y update
RUN apt-get -y install tesseract-ocr=3.04.01-5 --no-install-recommends
RUN apt-get -y install tesseract-ocr-deu=3.04.00-1 tesseract-ocr-jpn=3.04.00-1 tesseract-ocr-spa=3.04.00-1 --no-install-recommends

ENTRYPOINT ["/usr/bin/tesseract"]
