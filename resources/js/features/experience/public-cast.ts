export type PublicCastMember = {
    actor: string;
    character: string;
    role: string;
    code: string;
    summary: string;
    image: string;
    width: number;
    height: number;
    focalPosition?: string;
    sourceUrl: string;
    artist: string;
    license: string;
};

export const publicCast: readonly PublicCastMember[] = [
    {
        actor: 'Jared Padalecki',
        character: 'Sam Winchester',
        role: 'Hunter · Man of Letters',
        code: 'SW',
        summary:
            'Padalecki gives Sam an analytical, empathetic presence—the brother who keeps searching for another answer when the case appears closed.',
        image: '/media/cast/jared-padalecki.jpg',
        width: 960,
        height: 640,
        focalPosition: 'center 30%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Jared_Padalecki_by_Gage_Skidmore2.jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 2.0',
    },
    {
        actor: 'Jensen Ackles',
        character: 'Dean Winchester',
        role: 'Hunter · Man of Letters',
        code: 'DW',
        summary:
            'Ackles anchors Dean with instinct, loyalty, and dark humor, carrying the cost of the hunt even when he refuses to name it.',
        image: '/media/cast/jensen-ackles.jpg',
        width: 960,
        height: 640,
        focalPosition: 'center 30%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Jensen_Ackles_(9362265831).jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 2.0',
    },
    {
        actor: 'Misha Collins',
        character: 'Castiel',
        role: 'Angel · Ally',
        code: 'CA',
        summary:
            'Collins plays Castiel with stillness and curiosity, turning an otherworldly soldier into one of the Winchesters’ closest allies.',
        image: '/media/cast/misha-collins.jpg',
        width: 960,
        height: 1213,
        focalPosition: 'center 24%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Misha_Collins_by_Gage_Skidmore.jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 3.0',
    },
    {
        actor: 'Mark Sheppard',
        character: 'Crowley',
        role: 'Demon · King of Hell',
        code: 'CR',
        summary:
            'Sheppard makes Crowley precise, dangerous, and unexpectedly funny—a dealmaker whose help always arrives with conditions.',
        image: '/media/cast/mark-sheppard.jpg',
        width: 960,
        height: 1302,
        focalPosition: 'center 24%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Mark_A._Sheppard_by_Gage_Skidmore.jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 3.0',
    },
    {
        actor: 'Jim Beaver',
        character: 'Bobby Singer',
        role: 'Hunter · Mentor',
        code: 'BS',
        summary:
            'Beaver brings Bobby’s hard-earned knowledge and rough affection to the hunters who rely on him as mentor, researcher, and family.',
        image: '/media/cast/jim-beaver.jpg',
        width: 960,
        height: 1275,
        focalPosition: 'center 24%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Jim_Beaver_by_Gage_Skidmore.jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 3.0',
    },
    {
        actor: 'Alexander Calvert',
        character: 'Jack Kline',
        role: 'Nephilim · Family',
        code: 'JK',
        summary:
            'Calvert gives Jack an open, searching quality as he learns what power, choice, and belonging mean among the Winchesters.',
        image: '/media/cast/alexander-calvert.jpg',
        width: 960,
        height: 1274,
        focalPosition: 'center 22%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Alexander_Calvert_(48478081066)_(cropped).jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 2.0',
    },
    {
        actor: 'Ruth Connell',
        character: 'Rowena MacLeod',
        role: 'Witch · Queen of Hell',
        code: 'RM',
        summary:
            'Connell plays Rowena with theatrical confidence and sharp intelligence, balancing ruthless ambition with an evolving capacity for sacrifice.',
        image: '/media/cast/ruth-connell.jpg',
        width: 960,
        height: 1141,
        focalPosition: 'center 20%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Ruth_Connell_by_Gage_Skidmore.jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 3.0',
    },
    {
        actor: 'Kim Rhodes',
        character: 'Jody Mills',
        role: 'Sheriff · Hunter',
        code: 'JM',
        summary:
            'Rhodes gives Jody practical courage and steady compassion, showing how an ordinary sheriff becomes an essential hunter and protector.',
        image: '/media/cast/kim-rhodes.jpg',
        width: 960,
        height: 640,
        focalPosition: 'center 22%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Kim_Rhodes_in_Cleveland,_OH,_2018.jpg',
        artist: 'Megan Jackson',
        license: 'CC BY-SA 4.0',
    },
    {
        actor: 'Felicia Day',
        character: 'Charlie Bradbury',
        role: 'Hacker · Hunter',
        code: 'CB',
        summary:
            'Day makes Charlie brilliant, irreverent, and brave—a hacker whose curiosity leads her from fantasy worlds into the hunters’ reality.',
        image: '/media/cast/felicia-day.jpg',
        width: 960,
        height: 1370,
        focalPosition: 'center 20%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Felicia_Day_(42917965544)_(cropped).jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 2.0',
    },
    {
        actor: 'Rob Benedict',
        character: 'Chuck Shurley',
        role: 'Prophet · Writer',
        code: 'CS',
        summary:
            'Benedict presents Chuck as nervous, observant, and strangely connected to the story unfolding around the Winchesters.',
        image: '/media/cast/rob-benedict.jpg',
        width: 960,
        height: 1347,
        focalPosition: 'center 20%',
        sourceUrl:
            'https://commons.wikimedia.org/wiki/File:Rob_Benedict_by_Gage_Skidmore.jpg',
        artist: 'Gage Skidmore',
        license: 'CC BY-SA 3.0',
    },
];
