FROM ubuntu:precise

RUN apt-get -y update
RUN apt-get -y install tesseract-ocr=3.02.01-2
RUN apt-get -y install tesseract-ocr-deu tesseract-ocr-jpn tesseract-ocr-spa

ENTRYPOINT ["/usr/bin/tesseract"]
