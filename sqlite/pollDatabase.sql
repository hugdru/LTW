SQLite format 3   @                                                                     -�   �    ���                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  �  �m��                                                                                                                                                           �?�QtableUserDataUserDataCREATE TABLE UserData (
    idUser INTEGER AUTO INCREMENT,
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
    PRIMARY KEY (idUser))/C indexsqlite_autoindex_UserData_1UserData/C indexsqlite_autoindex_UserData_2UserData/C indexsqlite_autoindex_UserData_3UserData
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  �  ���                                                                                                                   �##�ctablePollEnquiryPollEnquiryCREATE TABLE PollEnquiry (
    idPollEnquiry INTEGER AUTO INCREMENT,
    name TEXT NOT NULL,
    dateCreation DATE NOT NULL,
    synopsis TEXT NOT NULL,
    conclusion TEXT,
    generatedLink TEXT,
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
    UNIQUE (name, dateCreation, idUser),
    PRIMARY KEY (idPollEnquiry))5I# indexsqlite_autoindex_PollEnquiry_1PollEnquiry5I# indexsqlite_autoindex_PollEnquiry_2PollEnquiry	   � ��                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                	 Closed Open
   � ��                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             	    �  �w�@u                                                                                                                                                                                                                                ��ctableStateStateCREATE TABLE State (
    idState INTEGER AUTO INCREMENT,
    name TEXT NOT NULL,
    PRIMARY KEY (idState)))	= indexsqlite_autoindex_State_1State�
!!�tableVisibilityVisibilityCREATE TABLE Visibility (
    idVisibility INTEGER AUTO INCREMENT,
    name TEXT NOT NULL,
    PRIMARY KEY (idVisibility))3G! indexsqlite_autoindex_Visibility_1Visibility��stablePollPollCREATE TABLE Poll (
    idPoll INTEGER AUTO INCREMENT,
    image TEXT, -- file path
    options TEXT NOT NULL, -- opcoes e tipo de poll, formato json
    idPollEnquiry INTEGER NOT NULL,
    FOREIGN KEY (idPollEnquiry)
        REFERENCES PollEnquiry(idPollEnquiry)
            ON DELETE SET NULL
            ON UPDATE CASCADE,
    UNIQUE (image),
    PRIMARY KEY (idPoll))   � ��                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             
 Private	 Public
   � ��                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 r r���                                                                                                                                                                                                                                                                                                                                                                  '; indexsqlite_autoindex_Poll_1Poll'; indexsqlite_autoindex_Poll_2Poll�|))�3tableUserPollAnswerUserPollAnswerCREATE TABLE UserPollAnswer (
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
    PRIMARY KEY (idPoll, idUser));O) indexsqlite_autoindex_UserPollAnswer_1UserPollAnswer