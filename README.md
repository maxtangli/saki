# saki
A japanese-mahjong solver.

## good practice

- Agile, TDD, KISS, bad-design WINs over-design

## bad smell

- [ ] ==,===,array_search => custom equalsTo() ?
- [ ] ugly const REGEX_XXX =>  class TileRegex ?
- [ ] static factory method such as fromString($s) force subclasses keep constructor signature => move to factory class?
- [x] ArrayObjectLike modify methods => protected methods in ArrayObjectLike
- [x] ArrayObjectLike protected methods override is buggy => refactor to public

## todo

rush 0 scribble 5/29 1.5h

- [x] terms in english
- [x] new a Tile
- [x] Tile.toString()
- [x] new a Hand

rush 1 init pj 6/2 1h

- [x] init pj
- [x] refactor&test
- [x] Hand.toString()

rush 2 judge pinfu 6/3 3h 6/9 2h

- [x] MeldTypes
- [x] new a TileSequence 
- [x] Hand.getMeldCompositions()
- [x] refactor ArrayLikeObject

rush 3 refactor 6/4 5.5h 6/6 2.5h

- [x] refactor Tile&TileList
- [x] Enum
- [x] Tile: refactor, fromString()
- [x] TileList: refactor, fromString()
- [x] Meld: refactor
- [x] Singleton override return
- [x] fulfil test cases

rush 4 hand 6/7 4h
- [x] simple UI: TileList
- [x] simple UI: remove a Tile
- [x] Hand
- [x] readonly TileList&TileOrderedList
- [x] Meld fromString
- [x] MeldList fromString
- [x] given a TileList, analyze MeldType
- [x] refactor Meld, replace inheritance with association

rush 5 round flow: private phase 6/5 2.5h 6/7 1.5h 6/9 1h 6/10 4h 6/11 1h 6/12 3h 6/13 1.5h
- [x] Wall
- [x] TurnManager
- [x] new Round

- [x] detailed logic analyze
- [x] RoundPhase
- [x] PrivatePhase: getCandidateCommands()

- [x] simple UI

- [x] PrivatePhase: execute command and to next turn
- [x] RoundCommand toString()/fromString()
- [x] bug: InfiniteIterator buggy with session
- [x] refactor: least RoundCommand to KISS  
- [x] Player toString()/fromString()
- [x] child commands fromString() 

rush 6 exhaustive draw: first step

- [x] DeadWall 0.5h
- [x] refactor Wall 0.5h
- [x] after command execute

rush 7 phase flow

- [x] detailed analyze 1h
- [x] PublicPhaseCommandPoller 0.5h
- [x] phase switch 2.5h
- [x] public phase: chow 0.5h
- [x] refactor Round to support test for chow 0.5h
- [x] public phase: pon/kong 0.5h
- [x] bug: turn calculate 1.0h

rush 8 refactor ArrayLikeObject

- [x] analyze 1.0h
- [x] implement iterate 0.5h
- [x] implement retrieve 2h
- [x] implement update 0.5h
- [x] implement insert 0.5h
- [x] implement delete 1h
- [x] implement keep-sorted
- [x] refactor usages 0.5h

rush 9 concealed triple/quad

- [x] concealed meld 1h
- [x] refactor: PlayerArea.candidateTile not convenient 0.2h
- [x] PlayerArea.canXXX 0.3h
- [x] refactor public phase chow/pong/kong 0.3h
- [x] private phase: kong, plusKong 0.7h

rush 10 first yaku impl: all runs yaku

- [x] pseudocode 3.5h
- [x] yaku.xls 1.5h
- [x] refactor: rename terms to follow yaku names 0.5h
- [x] organize code 1.0h
- [x] is4WinSetAnd1Pair() 0.2h
- [x] allRunsYaku 1.1h

rush 11 more yaku

- [x] ReachYaku 0.7h
- [x] WinState 0.7h
- [x] ValueTilesYaku 0.6h
- [x] AllSimplesYaku
- [x] test 0.2h

rush 12 

rush red dora

rush win

- [ ] private phase: win on self
- [ ] public phase: win on other

rush next round

- [ ] over phase: drawn
- [ ] over phase: ron/zimo

rush game over

features

- rush fushu/point
- rush dora/ red dora
- rush player wind
- more friendly UI

advanced features

- multi-media
- player AI
- replay
- player statistics

more advanced features

- tenhou client AI
- chating AI
- skill mode
- training mode

## note

Command

- RoundCommand operates on round, Round do not know Concrete Command? thus adding new Command will be easy?
- CommandAnalyzer list all possible commands with a given Round.

Command serialize

- toString discard p1 4p
- fromString DiscardCommand $round $player $params

## note

after 1 tile discarded, for every player judge:

    possibleTileList = onHand + candidate
    possibleMeldList = analyzeMeld candidateTileList
    yakuList = analyzeYaku possibleMeldList, exposedMeldList, discardedTileList, isLizhi, turn ...

isWaitingHand? iterate all possible candidate tile -> winning tile
TileSet.getUniqueTiles
todo zhengting?

    onHandMeldLists = MeldAnalyzer.analyze(onHandTileList)
    totalMeldLists = onHandMeldLists merge declaredMeldLists

foreach onHandMeldList
analyze yakuList
return yakulist.highest yaku ones

## note: win state

- not win tiles: win tiles not exist
- discarded win tile: win tiles exist but pinfu discarded
- no yaku: win tiles exist but yaku count = 0
- win: win tiles exist and yaku count > 0

## note: plus kong

in private phase

- plusKongBySelf: plus declared triple meld with 1 self tile. meld isExposed or isConcealed (keep).
- kongBySelf: turn 4 self tile into a quad meld. meld isConcealed (new).

in public phase

- plusKongByOther: plus declared triple meld with 1 other tile. meld isExposed (force).
- kongByOther: turn 3 self and 1 other tile into a quad meld. meld isExposed (new).

## note: ArrayLikeObject

main responsibility

- support foreach/count/isset/get/(set)
- encapsulate PHP array operations

modify operations visibility

- all: extends ArrayLikeAccessable
- none: implement ArrayLikeReadable and delegate to ArrayLikeAccessable
- some: implement ArrayLikeReadable and delegate to ArrayLikeAccessable

common operations

- create by v[] (constructor)

- retrieve k/k[]->all exist?
- retrieve k/k[]->v/v[] ($this[])
- retrieve v/v[]->all exist?
- retrieve v/v[]->k/k[]

- update v/v[] at k/k[] -> newV/newV[] ($this[]=)
- update all v[] -> new v[]

- insert pos v/v[], rearrange key
- insert unshift v/v[], rearrange key
- insert push v/v[], rearrange key

- delete k/k[] (unset($this[])) rearrange key
- delete shift->v/v[]
- delete pop->v/v[]

support keep-sorted

- modifiedHook()

## note: round logic

new phase

- reset and shuffle wall
- decide dealer player
- decide each player's wind

init phase

- each player draw 4 tiles
- goto dealer player's private phase
 
p's private phase: before execute command

- when enter: turn++, draw 1 tile if allowed
- show candidate commands
- always: discard one of onHand tile 
- sometime: kong, plusKong, zimo

p's private phase: after execute command

- if discard: go to public phase
- if zimo: go to round-over phase
- if kong/plusKong: drawBack, stay in private phase

p's public phase: basic version

- public phase means waiting for other's response for current player's action
- poller responsible for select a final action for public phase

p's public phase: before execute command

- only non-current players may have candidate commands
- if none candidate commands exist: goto next player's private phase if remainTileCount > 0, otherwise go to over phase
- if candidate commands exist, wait for each player's response, and execute the highest priority ones.
- command types: ron, chow, pon, kong

p's public phase: after execute command

- if ron: go to round-over phase
- if chow/pon/kong: go to execute player's private phase?

over phase

- draw or win
- calculate points and modify players' points
- new next round

## note: getMelds(draft)

getMeldCompositions(tileList)

-idea: first tile should belong to one of the meld, otherwise empty result
-solution:

> list all possible meld that contains first tile
> foreach possibleMelds as firstMeld
>   reaminedTileList = tileList->remove($meld->toArray())
>   remainedMeldLists = getMeldCompositiosn(tileList)
>   if remainedMeldLlists not empty, merge firstMeld with remainedMeldLists

list all possible meld that contains first tile

getMelds(TileList, MeldType)

- return getMeldsThatContainsFirstTile(TileList, MeldType) merge getMelds(TileList.remove(0), MeldType)

getMeldsThatContainsFirstTile(TileList, MeldType)

getMeldsThatContainsTile(TileList, MeldType, Tile)

- sort+unique

getCandidateMelds($tileList, $candidateTile, $meldTypes)

simple soulution reuse getMeldCompositions:

- add tile into newTileList and sort
- getMeldLists
- return meld in meldLists where meld contians candidateTile