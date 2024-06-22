version: '3.9'

services:
  chroma:
    image: 'chromadb/chroma'
    ports:
      - '8000:8000'
    volumes:
      - chroma-data:/chroma/chroma

volumes:
  chroma-data:
    driver: local
