PRAGMA foreign_keys = ON;
PRAGMA encoding = "UTF-8";

CREATE TABLE IF NOT EXISTS UserData (
    idUser INTEGER,
    -- Login Data
    email TEXT NOT NULL,
    -- native php password_hash generates $hashAndRandomSalt, to avoid pre-computed attacks like:
    -- reverse dictionary attacks and Rainbow table attacks. So there is no need to create another
    -- member called salt. The native function does something like hash(password+salt) . $salt
    hashPlusSalt TEXT NOT NULL,
    -- Bot control
    loginAttempts INTEGER NOT NULL,
    lastLoginDate DATE NOT NULL,
    -- Additional User data
    username TEXT NOT NULL,
    lastIp TEXT NOT NULL,
    about TEXT,
    UNIQUE(email), UNIQUE(username),
    PRIMARY KEY (idUser));

CREATE TABLE IF NOT EXISTS Poll (
    idPoll INTEGER,
    name TEXT NOT NULL,
    dateCreation DATE NOT NULL,
    synopsis TEXT,
    conclusion TEXT,
    generatedKey TEXT,
    image TEXT, -- file path
    idUser INTEGER NOT NULL,
    idState INTEGER NOT NULL,
    idVisibility INTEGER NOT NULL,
    FOREIGN KEY (idState)
        REFERENCES State(idState)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    FOREIGN KEY (idVisibility)
        REFERENCES Visibility(idVisibility)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    FOREIGN KEY (idUser)
        REFERENCES UserData(idUser)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    UNIQUE (generatedKey),
    UNIQUE (name, dateCreation, idUser),
    PRIMARY KEY (idPoll));

CREATE TABLE IF NOT EXISTS State (
    idState INTEGER,
    name TEXT NOT NULL,
    PRIMARY KEY (idState));

CREATE TABLE IF NOT EXISTS Visibility (
    idVisibility INTEGER,
    name TEXT NOT NULL,
    PRIMARY KEY (idVisibility));

CREATE TABLE IF NOT EXISTS Question (
    idQuestion INTEGER,
    options TEXT NOT NULL, -- opcoes, formato json
    description TEXT,
    idPoll INTEGER NOT NULL,
    FOREIGN KEY (idPoll)
        REFERENCES Poll(idPoll)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    PRIMARY KEY (idQuestion));

CREATE TABLE IF NOT EXISTS UserQuestionAnswer (
    idQuestion INTEGER,
    idUser INTEGER,
    dateDone DATE NOT NULL,
    optionSelected TEXT NOT NULL, -- option selected, in json format
    FOREIGN KEY (idQuestion)
        REFERENCES Question(idQuestion)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    FOREIGN KEY (idUser)
        REFERENCES UserData(idUser)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    PRIMARY KEY (idQuestion, idUser));
