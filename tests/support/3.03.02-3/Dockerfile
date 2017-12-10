FROM ubuntu:trusty
MAINTAINER thiagoalessio <thiagoalessio@me.com>

RUN apt-get -y update
RUN apt-get -y install tesseract-ocr=3.03.02-3 --no-install-recommends
RUN apt-get -y install tesseract-ocr-deu=3.02-2 tesseract-ocr-jpn=3.02-2 tesseract-ocr-spa=3.02-2 --no-install-recommends

ENTRYPOINT ["/usr/bin/tesseract"]
