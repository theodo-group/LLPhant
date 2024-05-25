CREATE EXTENSION vector;

CREATE TABLE IF NOT EXISTS test_place (
                                          id SERIAL PRIMARY KEY,
                                          content text,
                                          type text,
                                          sourcetype text,
                                          sourcename text,
                                          embedding vector
);
