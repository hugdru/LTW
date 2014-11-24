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

CREATE TABLE IF NOT EXISTS PollEnquiry (
    idPollEnquiry INTEGER,
    name TEXT NOT NULL,
    dateCreation DATE NOT NULL,
    synopsis TEXT NOT NULL,
    conclusion TEXT,
    generatedKey TEXT,
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
    PRIMARY KEY (idPollEnquiry));

CREATE TABLE IF NOT EXISTS State (
    idState INTEGER,
    name TEXT NOT NULL,
    PRIMARY KEY (idState));

CREATE TABLE IF NOT EXISTS Visibility (
    idVisibility INTEGER AUTO INCREMENT,
    name TEXT NOT NULL,
    PRIMARY KEY (idVisibility));

CREATE TABLE IF NOT EXISTS Poll (
    idPoll INTEGER,
    image TEXT, -- file path
    options TEXT NOT NULL, -- opcoes e tipo de poll, formato json
    idPollEnquiry INTEGER NOT NULL,
    FOREIGN KEY (idPollEnquiry)
        REFERENCES PollEnquiry(idPollEnquiry)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    UNIQUE (image),
    PRIMARY KEY (idPoll));

CREATE TABLE IF NOT EXISTS UserPollAnswer (
    idPoll INTEGER,
    idUser INTEGER,
    dataDone DATE NOT NULL,
    optionsSelected TEXT NOT NULL, -- options selected, in json format
    observations TEXT,
    FOREIGN KEY (idPoll)
        REFERENCES Poll(idPoll)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    FOREIGN KEY (idUser)
        REFERENCES UserData(idUser)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    PRIMARY KEY (idPoll, idUser));
