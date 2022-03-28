CREATE TABLE IF NOT EXISTS score (
                       name  VARCHAR (45) NOT NULL,
                       score INTEGER      NOT NULL,
                       time  DATETIME     NOT NULL
                           DEFAULT (strftime('%Y-%m-%d %H:%M:%f', 'now', 'localtime') )
);
