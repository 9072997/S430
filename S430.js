var bracket = new Meteor.Collection('bracket');
bracket.traceBracket = function(matchNumber, winner) {
    var match = this.findOne({match: matchNumber});
    if('team' in match) {
        // we are at the end of a branch
        return match.team;
    } else {
        // we have a normal node
        if(typeof winner === 'unefined') {
            winner = true;
        }
        if(match.winner === 'green' && winner || match.winner === 'blue' && !winner) {
            // we could want the green node because green won or because blue won & we are hunting the looser
            if('greenWinner' in match) {
                return this.traceBracket(match.greenMatch, match.greenWinner);
            } else {
                return this.traceBracket(match.greenMatch);
            }
        } else if(match.winner === 'blue' && winner || match.winner === 'green' && !winner) {
            if('blueWinner' in match) {
                return this.traceBracket(match.blueMatch, match.blueWinner);
            } else {
                return this.traceBracket(match.blueMatch);
            }
        } else {
            return '?????';
        }
    }
};
bracket.getCompetitors = function(matchNumber) {
    var match = this.findOne({match: matchNumber});
    var green, blue;
    if('greenWinner' in match) {
        green = this.traceBracket(match.greenMatch, match.greenWinner);
    } else {
        green = this.traceBracket(match.greenMatch);
    }
    if('blueWinner' in match) {
        blue = this.traceBracket(match.blueMatch, match.blueWinner);
    } else {
        blue = this.traceBracket(match.blueMatch);
    }
    return {green: green, blue: blue};
};

if (Meteor.isClient) {
    window.bracket = bracket;
    Session.set('time', 0);
    Session.set('master', false);
    Session.set('started', false);
    setInterval(function(){
            if(Session.get('time')>0) {
                Session.set('time', Session.get('time')-1); // decrese time variable by 1
            }
        }, 1000);
    Deps.autorun(function() {
        var running = bracket.findOne({running: {$exists: true}});
        if(typeof running !== 'undefined' && running.running) {
            if(Session.get('started')) {
                if(Session.get('time') <= 23) { //TIME/2
                    if(Session.get('master')) {
                        bracket.update(running._id, {$set: {running: false}});
                        Session.set('started', false);
                    }
                }
                if(Session.get('time') <= 0) {
                    Session.set('started', false);
                }
            } else {
                Session.set('time', 45); //TIME
                Session.set('started', true);
            }

        }
    });
    UI.body.queue = function() {
        var now = bracket.findOne({now: {$exists: true}});
        if(typeof now === 'undefined') { // this happens when we arn't yet synced with the database
            return {
                    previous: {won: '?????', lost: '?????'},
                    now: {blue: '?????', green: '?????'},
                    next: {blue: '?????', green: '?????'},
                    then: {blue: '?????', green: '?????'},
                    later:{blue: '?????', green: '?????'}
                };
        }
        var nowMatch = bracket.findOne({match: now.now});
        var nextMatches = bracket.find({order: {$gt: nowMatch.order}}, {sort: {order: 1}, limit: 3}).fetch();
        var previousMatch = bracket.findOne({order: {$lt: nowMatch.order}}, {sort: {order: -1}});
        var won, lost;
        if(typeof previousMatch === 'undefined') {
            won = 'XXXXX';
            lost = 'XXXXX';
        } else {
            won = bracket.traceBracket(previousMatch.match, true);
            lost = bracket.traceBracket(previousMatch.match, false);
        }
        return {
                previous: {won: won, lost: lost},
                now: bracket.getCompetitors(nowMatch.match),
                next: nextMatches.length > 0 ? bracket.getCompetitors(nextMatches[0].match) : {blue: 'XXXXX', green: 'XXXXX'},
                then: nextMatches.length > 1 ? bracket.getCompetitors(nextMatches[1].match) : {blue: 'XXXXX', green: 'XXXXX'},
                later: nextMatches.length >2 ? bracket.getCompetitors(nextMatches[2].match) : {blue: 'XXXXX', green: 'XXXXX'}
            };
    };
    UI.body.time = function() {
        if(Session.get('time') < 10) {
            return '0' + Session.get('time');
        } else {
            return Session.get('time');
        }
    };
    UI.body.winner = function() {
        var now = bracket.findOne({now: {$exists: true}});
        var standard = {
                blue: {class: 'white-on blue button'},
                green: {class: 'white-on green button'}
            }; // to make returns shorter
        if(typeof now === 'undefined') { // this happens when we arn't yet synced with the database
            return standard;
        }
        var match = bracket.findOne({match: now.now});
        if('winner' in match && match.winner === 'blue') {
            return {
                    blue: {class: 'black-on yellow button'},
                    green: {class: 'white-on green button'}
                };
        } else if('winner' in match && match.winner === 'green') {
            return {
                    blue: {class: 'white-on blue button'},
                    green: {class: 'black-on yellow button'}
                };
        } else {
            return standard;
        }
    };
    UI.body.master = function() {
        if(Session.get('master')) {
            return {class: 'black-on white button'};
        } else {
            return {class: 'white-on black button'};
        }
    };
    UI.body.tallScreen = function() {
        return window.innerHeight >= 980;
    }
    UI.body.events({
       'click #winner': function() {
            if(Meteor.status().connected) {
                var now = bracket.findOne({now: {$exists: true}}).now;
                var nowOrder = bracket.findOne({match: now}).order;
                var previousMatch = bracket.findOne({order: {$lt: nowOrder}}, {sort: {order: -1}});
                bracket.update(previousMatch._id, {$set: {winner: previousMatch.winner === 'blue' ? 'green' : 'blue'}});
            }
       },
       'contextmenu #time': function() {
           Session.set('master', !Session.get('master'));
           return false;
       },
       'click #blue': function() {
           if(Meteor.status().connected) {
                var now = bracket.findOne({now: {$exists: true}}).now;
                var match = bracket.findOne({match: now})._id;
                bracket.update(match, {$set: {winner: 'blue'}});
           }
       },
       'click #green': function() {
           if(Meteor.status().connected) {
                var now = bracket.findOne({now: {$exists: true}}).now;
                var match = bracket.findOne({match: now})._id;
                bracket.update(match, {$set: {winner: 'green'}});
           }
       },
       'click #next': function() {
           if(Meteor.status().connected) {
                var now = bracket.findOne({now: {$exists: true}});
                var endingOrder = bracket.findOne({match: now.now}).order;
                var startingMatch = bracket.findOne({order: {$gt: endingOrder}}, {sort: {order: 1}}).match;
                bracket.update(now._id, {$set: {now: startingMatch}});
           }
       },
       'click #time': function() {
           if(Meteor.status().connected) {
                var running = bracket.findOne({running: {$exists: true}})._id;
                bracket.update(running, {$set: {running: true}});
           }
       }
    });
}

if (Meteor.isServer) {
    Meteor.startup(function () {
        // code to run on server at startup
        bracket.remove({}, function() {
            bracket.insert({           match:   1, team: 'MANIACS'  });
            bracket.insert({           match:   2, team: 'KNIGHTS'  });
            bracket.insert({           match:   3, team: 'ANGELS'});
            bracket.insert({           match:   4, team: 'RIBBNS' });
            bracket.insert({           match:   5, team: 'AVNGRS' });
            bracket.insert({           match:   6, team: 'FERRETS'  });
            bracket.insert({           match:   7, team: 'SMELLY'});
            bracket.insert({           match:   8, team: 'SQUISHY'});
            bracket.insert({           match:   9, team: 'DARTH'});
            bracket.insert({order:  1, match: 101,                          greenMatch:   1,                         blueMatch:   2});
            bracket.insert({order:  2, match: 102,                          greenMatch:   3,                         blueMatch:   4});
            bracket.insert({order:  3, match: 103,                          greenMatch:   5,                         blueMatch:   6});
            bracket.insert({order:  4, match: 104,                          greenMatch:   7,                         blueMatch:   8});
            bracket.insert({order:  5, match: 105,                          greenMatch:   9,      blueWinner: true,  blueMatch: 101});
            bracket.insert({order:  6, match: 109,      greenWinner: true,  greenMatch: 102,      blueWinner: true,  blueMatch: 103});
            bracket.insert({order:  7, match: 110,      greenWinner: true,  greenMatch: 105,      blueWinner: true,  blueMatch: 104});
            bracket.insert({order:  8, match: 113,      greenWinner: true,  greenMatch: 109,      blueWinner: true,  blueMatch: 110});
            bracket.insert({order:  9, match: 106,      greenWinner: false, greenMatch: 101,      blueWinner: false, blueMatch: 102});
            bracket.insert({order: 10, match: 107,      greenWinner: false, greenMatch: 104,      blueWinner: false, blueMatch: 105});
            bracket.insert({order: 11, match: 108,      greenWinner: true,  greenMatch: 106,      blueWinner: false, blueMatch: 103});
            bracket.insert({order: 12, match: 111,      greenWinner: true,  greenMatch: 107,      blueWinner: false, blueMatch: 109});
            bracket.insert({order: 13, match: 112,      greenWinner: false, greenMatch: 110,      blueWinner: true,  blueMatch: 108});
            bracket.insert({order: 14, match: 114,      greenWinner: true,  greenMatch: 112,      blueWinner: true,  blueMatch: 111});
            bracket.insert({order: 15, match: 115,      greenWinner: false, greenMatch: 113,      blueWinner: true,  blueMatch: 114});
            bracket.insert({order: 16, match: 116,      greenWinner: true,  greenMatch: 113,      blueWinner: true,  blueMatch: 115});
            bracket.insert({order: 17, match: 117,      greenWinner: true,  greenMatch: 116,      blueWinner: false, blueMatch: 116});
            bracket.insert({now: 101});
            bracket.insert({running: false});
            bracket.ensureIndex({order: 1}, {background: true, sparse: true, unique: true});
            bracket.ensureIndex({match: 1}, {background: true, sparse: true, unique: true});
        });
  });
}