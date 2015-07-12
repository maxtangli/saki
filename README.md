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
- [ ] PlayerList pass as argument

## todo

rush 0 scribble 1.5h

- [x] terms in english
- [x] new a Tile
- [x] Tile.toString()
- [x] new a Hand

rush 1 init pj 1h

- [x] init pj
- [x] refactor&test
- [x] Hand.toString()

rush 2 judge pinfu 5h

- [x] MeldTypes
- [x] new a TileSequence 
- [x] Hand.getMeldCompositions()
- [x] refactor ArrayLikeObject

rush 3 refactor 8h

- [x] refactor Tile&TileList
- [x] Enum
- [x] Tile: refactor, fromString()
- [x] TileList: refactor, fromString()
- [x] Meld: refactor
- [x] Singleton override return
- [x] fulfil test cases

rush 4 hand 4h
- [x] simple UI: TileList
- [x] simple UI: remove a Tile
- [x] Hand
- [x] readonly TileList&TileOrderedList
- [x] Meld fromString
- [x] MeldList fromString
- [x] given a TileList, analyze MeldType
- [x] refactor Meld, replace inheritance with association

rush 5 round flow: private phase 14.5h
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

rush 6 exhaustive draw: first step 1h

- [x] DeadWall 0.5h
- [x] refactor Wall 0.5h
- [x] after command execute

rush 7 phase flow 6.5h

- [x] detailed analyze 1h
- [x] PublicPhaseCommandPoller 0.5h
- [x] phase switch 2.5h
- [x] public phase: chow 0.5h
- [x] refactor Round to support test for chow 0.5h
- [x] public phase: pon/kong 0.5h
- [x] bug: turn calculate 1.0h

rush 8 refactor ArrayLikeObject 6h

- [x] analyze 1.0h
- [x] implement iterate 0.5h
- [x] implement retrieve 2h
- [x] implement update 0.5h
- [x] implement insert 0.5h
- [x] implement delete 1h
- [x] implement keep-sorted
- [x] refactor usages 0.5h

rush 9 concealed triple/quad 2.5h

- [x] concealed meld 1h
- [x] refactor: PlayerArea.candidateTile not convenient 0.2h
- [x] PlayerArea.canXXX 0.3h
- [x] refactor public phase chow/pong/kong 0.3h
- [x] private phase: kong, plusKong 0.7h

rush 10 first yaku impl: all runs yaku 7.8h

- [x] pseudocode 3.5h
- [x] yaku.xls 1.5h
- [x] refactor: rename terms to follow yaku names 0.5h
- [x] organize code 1.0h
- [x] is4WinSetAnd1Pair() 0.2h
- [x] allRunsYaku 1.1h

rush 1-10 57.8h

rush 11 more yaku 2.2h

- [x] ReachYaku 0.7h
- [x] WinState 0.7h
- [x] ValueTilesYaku 0.6h
- [x] AllSimplesYaku
- [x] test 0.2h

rush 12 win flow 5.8h

- [x] analyze target 0.7h
- [x] use-case analyze 2.2h

- [x] remove TurnManager into PlayerList 0.5h
- [x] bug: not drawn. cause; PlayerList sharable -> unsharable 0.7h
- [x] adapt Round with PlayerList 0.3h

- [x] private phase: win on self 0.8h
- [x] over phase: exhaustive drawn 0.6h

rush 13 score table 4.6h

- [x] score table 3.3h
- [x] analyze 0.6h
- [x] refactor PlayerList 0.4h
- [x] bug: RoundResult.getOriginScore() 0.3h

rush 14 next round 1.4h

- [x] RoundData 0.3h
- [x] reach condition 0.2h
- [x] PlayerList.reset() 0.2h
- [x] refactor: move $wall into RoundData 0.1h
- [x] analyzerTarget constructor: add $roundData 0.1h
- [x] toNextRound() 0.2h
- [x] roundResult->getNextPlayer() 0.3h

rush 15 game over

- [x] refactor: 0.4h
- [x] finalScore 1h
- [x] result: winner order, final score 0.4h

rush 16 game over fix

- [x] isLastRound() 0.6h
- [x] refactor: move PlayerList into RoundData 1.1h
- [x] bug: PlayerList  0.4h

rush etc: normal priority
- [ ] east-south round
- [ ] bug: getTopPlayer() is wrong when same score top players exist
- [ ] bug: getMeldCompositions() won't count for 112233s like tiles

rush etc: low priority
- [ ] initial score rule
- [ ] refactor: move DrawScore logic into separate class

rush win score

- [ ] fu count
- [ ] reach score
- [ ] round n score

rush win/draw detail

- [ ] public phase: win on other
- [ ] public phase: multiple win on other

rush draw detail

- [ ] over phase: four kong drawn
- [ ] over phase: four reach drawn
- [ ] over phase: 9-kind 9-tile drawn

rush all yaku

- [ ] test design
- [x] not-count-yaku logic
- [x] yaku-man logic 1h

rush red dora

- [ ] discard/createMeld logic
- [ ] impl

rush simple ui

rush candidate commands
rush server logic

advanced features

- multi player
- multi-media
- player AI
- replay
- player statistics

## note: round over & game over

isGameOver

NorthRoundWind 4 SelfRoundWind 0 -> gameover 

LastRoundWind 4 or extraRoundWind any  
- 




## note: game over score

順位ウマ 4位と3位の者から1位と2位の者に支払われる
+20
+10
-10
-20

オカ トップ者が総取りする
配給原点（はいきゅうげんてん） 半荘開始時の持ち点
原点（げんてん） 半荘終了時の成績評価に使う

25000 -> 31100 +1100 +1.1 +2 +20 = +22
25000 -> 24400 -5600 -5.6 -6     = -6
25000 -> 22300 -7700 -7.7 -8     = -8
25000 -> 22200 -7800 -7.8 -8     = -8

result tpl

rank player finalScore scorePoint
1st p1 31100 +42
2nd p2 24400 +4
3rd p3 22300 -18
4th p4 22200 -28

## note: overPhase

types

- exhaustive draw: each player isWaiting? scores delta depend on isWaiting.
- winOnSelf: winPlayer, fuCount, fanCount, yakuList. scores depend on fu/fanCount + isDealer. 

scores delta format? 
 
- show origin score& delta

when to modify player scores?

- toOverPhase(): modify scores, keep scoreDeltaInfo
- view: show result type -> show result type concerned info -> show scoreDeltaInfo

## note: to next round

if game over
 show GameResult
else nextDealer = $roundResult.getNextDealer
return new Round($playerList, $nextDealer)

game over: lastRound, nextDealer != dealer

## note: GameResult

1st p1 45000 +5000 etc-rank-concerned-info
...
...
...

## note: RoundResult

roundWind roundWindTurn selfWindTurn
(East) (4) (Round 2)

no selfWind roundTurn score
(p1) (South) (turn 10) (score 40000)
...
...
...

## note: result.tpl $roundResult (pass required info to decouple from Round)

- winOnSelf

p1 winOnSelf

yaku1 1ban
yaku2 1ban
dora  2ban

70fu 4ban +7700(total scoreDelta = baseScoreDelta + reachScoreDelta + dealerScoreDelta)

p1 xxxx + 7700 -> xxxx
p2 xxxx - xxxx -> xxxx
p3 xxxx - xxxx -> xxxx
p4 xxxx - xxxx -> xxxx

next round = winnner is dealer ? winner : dealer's next

- exhaustive draw

p1 waiting
p2 waiting
p3 waiting
p4 not waiting

p1 xxxx + 7700 -> xxxx
p2 xxxx - xxxx -> xxxx
p3 xxxx - xxxx -> xxxx
p4 xxxx - xxxx -> xxxx

next round = dealer is waiting ?

## note: Command

Command

- RoundCommand operates on round, Round do not know Concrete Command? thus adding new Command will be easy?
- CommandAnalyzer list all possible commands with a given Round.

Command serialize

- toString discard p1 4p
- fromString DiscardCommand $round $player $params

## note: isWait

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