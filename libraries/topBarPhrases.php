<?php
// Assigns a random phrase to the top bar
// of the portal. This is just for fun and can be easily modified or removed.


    function DisplayRandomTopbarPhrase(){
        $topbarArtistPhrases = [
            "Welcome to the Simeck Dropbox platform!",
            "Are we having beautiful weather in your area? I sure hope so!",
            "Have you checked out the Resources share? It's got some neat stuff.",
            "Did you know that you can share files directly to the Discord chat? Try it out!",
            "Art with integrity, that's our motto!",
            "Proudly hand-coded by a human. Like a psychopath.",
            "If you have questions, please don't hesitate to reach out to your Project lead, or Randy/Carl.",
            "The platform won't bug you to change your password periodically, but it's still good practice to do so.",
            "Never gonna give you up, never gonna let you down, never gonna run around and desert you.",
            "Comments? Suggestions? Concerns? Feel free to reach out to Randy or Carl, or your project lead!",
            "This whole platform is built on weaponized laziness. If you have an idea on something we can automate, let us know!",
            "Have you eaten today? Remember to take care of yourself! Hydrate, eat, and stretch! Your health is more important than any deadline.",
            "Take breaks. Step outside. Breathe. Touch grass. Your work will be better for it, and so will you.",
            "If you're struggling more than a one-armed cowboy trying to lasso a prize bull, remember stop and ask for help.",
            "Clients will always be clients. If you're struggling with yours, please reach out for help."
            ];

        $topbarClientPhrases = [
            "Welcome to the Simeck Dropbox platform!",
            "This is where you can access and download your project files.",
            "If you have any questions or need assistance, please reach out to your Project lead or contact Randy/Carl.",
            "Remember to check back regularly for updates on your project files.",
            "We appreciate your patronage and look forward to delivering great work for you!",
            "Proudly hand-coded by a human."
        ];
        switch(GetTempRole()){
            case 'admin':
                return GetRandomPhrase($topbarArtistPhrases);
            case 'artist':
                return GetRandomPhrase($topbarArtistPhrases);
            case 'client':
                return GetRandomPhrase($topbarClientPhrases);
            default:
                return '';
        }
    }
    function GetRandomPhrase($phraseArray){
        $randomIndex = array_rand($phraseArray);
        return $phraseArray[$randomIndex];
    }