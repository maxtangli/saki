# saki

A japanese-mahjong server.

## good practice

- agile = tdd + kiss + refactor

## todo

total time cost

- rush  0- 5 34h
- rush  6-10 23.8h
- rush 11-15 15.8h
- rush 16-20 25.6h
- rush 21-25 26.7h
- rush 26-30 ?h
-      total 125.9h + ?h

rush 0 scribble 1.5h

- [x] terms in english
- [x] new a Tile
- [x] Tile.toString()
- [x] new a Hand

rush 1 reset pj 1h

- [x] reset pj
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

rush 15 game over 1.8h

- [x] refactor: 0.4h
- [x] finalScore 1h
- [x] result: winner order, final score 0.4h

rush 16 game over fix 2.1h

- [x] isLastRound() 0.6h
- [x] refactor: move PlayerList into RoundData 1.1h
- [x] bug: PlayerList  0.4h

rush 17 win issues 1.6h

- [x] reach score
- [x] round n score 0.3h
- [x] game over: minus score 0.1h
- [x] WinRoundResult 0.5h
- [x] public phase: win on other 
- [x] public phase: multiple win on other 0.4h
- [x] test design 0.3h

rush 18 refactor 5h

- [x] refactor: score strategy 2h
- [x] refactor: TileAreas 0.5h
- [x] refactor: RoundWindData 1.5h
- [x] refactor: accumulatedReachCount, remove unnecessary methods in Round&RoundData. 0.8h
- [x] bug: getTopPlayer() is wrong when same score top players exist 0.2h

rush 19 WinAnalyzer issues 8.2h

- [x] refactor: validCount, winState 0.5h
- [x] adpat public phase 0.7h
- [x] waitingType 4h
- [x] fu count 3h

rush 20 WaitingAnalyzer 8.7h

- [x] PublicPhase case: 3.6h // note: all children's constructor calls should be modified when new field added in parent
- [x] PrivatePhase case: 1h // too slow
- [x] speed up of WaitingAnalyzer.analyzePrivatePhase: 3.8h // 700ms -> 120ms, 6 times faster
- [x] adapt isWaiting 0.3h

rush 21 refactor tons, fix bugs 8h

- [x] refactor Meld/WeakMeld: 2h
- [x] add equal logic for ArrayLikeObject: 0.5h
- [x] refactor WinSetType: 0.7h
- [x] refactor MeldType, add MeldType.getWaitingType(): 1.5h
- [x] Meld.fromWeakMeldWaitingType(): 0.6h
- [x] refactor MeldList isFourXXXAndOneXXX(): 0.6h
- [x] refactor TileSeriesType: 2.1h

rush 22 fix getMeldCompositions() 1.5h

- [x] bug: getMeldCompositions() won't count for 112233s like tiles 1.3h
- [x] bug: MeldCompositionAnalyzer: exposed 0.2h

rush 23 WinAnalyzer: furiten 14.7h

- [x] analyze waiting and furiten 2.2h
- [x] TileSeries.getWaitingTiles 1.3h // unnecessary?
- [x] refactor: analyzeWaitingTiles 2.1h
- [x] refactor: move Utils.ArrayXXX into ArrayLikeObject 0.5h
- [x] refactor: compareTo 1.3h
- [x] analyzeWinTarget.mergeSubTargets 1.2h // finally all tests passed again >_<
- [x] refactor: getHandList(includeTarget?) 0.2h

- [x] replace local turn with global turn 0.7h
- [x] Tile.toNext(offset) 0.8h

- [x] DiscardHistory 1.5h
- [x] DiscardHistoryTest 0.9h 

- [x] adapt DiscardHistory to finish isDiscardedTileFalseWin 0.2h
- [x] add reach turn 0.5h
- [x] furiten final test 1.3h // finally this long long rush finished !

rush 24 WinByOther, MultipleWinByOther 1h

- [x] round.winByOther 0.2h
- [x] test MultipleWinByOther 0.3h
- [x] refactor: remove WinRoundResult subclasses 0.3h
- [x] test Round.winByXXX 0.2h

rush 25 OnTheWayDrawRoundResult 1.5h

- [x] add RoundResultType 0.4h
- [x] 9-kind 9-tile drawn, keep dealer 0.2h
- [x] four wind drawn, keep dealer 0.3h
- [x] four kong drawn, keep dealer 0.4h
- [x] four reach drawn, keep dealer 0.2h

rush 26 all yaku: simple ones 12.6h

- [x] not-count-yaku logic
- [x] yaku-man logic 1h
- [x] other simple yakus day1 1.8h
- [x] other simple yakus day2 3h
- [x] test design 1.5h
- [x] refactor: merge private/publicTargetTile 1.1h
- [x] test fan1 yakus 1h
- [x] test fan2 yakus 1.3h
- [x] bug: Meld.isXXXWinSet() 0.4h
- [x] bug: FullStraight 1h // Meld.equals issues; forget to write return for
- [x] test fan3,fan6 yakus 0.2h
- [x] test yakuman/yakuman2 yakus 0.3h

rush 27 refactor: concealed 1.7h

- [x] refactor: remove isExposed, use isConcealed instead 1.7h
- [x] Meld
- [x] MeldList
- [x] TileArea.declareMeld
- [x] Yaku, YakuList
- [x] MeldCompositionAnalyzer
- [x] YakuAnalyzer
- [x] grep exposed

rush 28 refactor for beauty 5.9h

- [x] refactor: move Round.roundResult into RoundData 0.2h
- [x] refactor: organize WinTarget 0.2h
- [x] refactor: organize TileAreas, PlayerArea 0.2h

- [x] TurnManager 0.7h
- [x] Roller 1.4h
- [x] refactor: move RoundData.$roundResult into $turnManager 0.1h
- [x] refactor: move RoundData.$roundPhase into $turnManager 0.2h
- [x] refactor: move RoundData.$playerList's rolling role into $turnManager 1.2h

- [x] refactor: move TileArea.init() into TileAreas 0.3h
- [x] refactor: for ArrayLikeObject: test more, add writable, refactor valueToIndex series 1.4h

rush 29 all yaku: reach concerned 2.8h

- [x] refactor: AbstractValueTilesYaku 0.2h
- [x] DeclareHistory 0.3h
- [x] RoundTurn 0.4h
- [x] YakuSet 0.6h // batch is not necessary and too much labour here
- [x] test reach, doubleReach 0.4h
- [x] bug: wrongly turn into over phase after pass N's public phase 0.1h // It's not a bug but test case results in FourWindDraw!
- [x] FirstTurnWinYaku 0.5h
- [x] test FirstTurnWinYaku 0.2h // wrongly written pastTurn <= 0, should be pastTurn < = 1

rush all yaku: kong concerned

- [ ] organize doc 0.2h
- [ ] refactor: RoundState
- [ ] refactor: introduce RoundTurn 0.2h // not sure whether necessary or not

- [ ] research 0.4h
- [ ] Kong Public Phase
- [ ] KingTileWinYaku
- [ ] RobbingAQuadYaku

rush 31 all yaku: HeavenlyWin, EarthlyWin, HumanlyWin

- [ ] fix FinalTileWin Fish/Moon 0.1h 
- [ ] HeavenlyWin
- [ ] EarthlyWin
- [ ] HumanlyWin

rush all yaku: thirteen orphans

- [ ] thirteen orphan meld

rush command system

- [ ] command 0.2h
- [ ] public command roller

rush refactor

- [ ] refactor: move MockRound/YakuTestData methods into RoundData members
- [ ] refactor: TileSeries <-> MeldList.xxx
- [ ] refactor: RoundTest
- [ ] refactor: RoundResult
- [ ] refactor: move DrawScore logic into separate class

rush rule.md doc

- [ ] rule 0.6h
- [ ] tile 0.6h
- [ ] yaku 1.1h

rush dora yaku

- [ ] design 0.1h
- [ ] doraYaku
- [ ] uraDoraYaku
- [ ] redDoraYaku

rush red dora

- [x] Tile.getInstance() 0.3h
- [x] Tile.isRed() 0.2h
- [x] Tile.getID() 0.3h
- [ ] toString issues
- [ ] adapt discard/createMeld logic

rush optimize to speed up tests

- [x] optimize: new Round().drawInit 1.8h // ArrayLikeObject.pop() 5ms -> 1ms by simplify, Wall().draw 66ms -> 3ms by fix wrongly use of TileSortedList
- [ ] optimize: new RoundData() 0.5h // TileSet extends TileSortedList -> extends TileList
- [ ] refactor: speed up slow tests
- [x] bug: Timer not accurate since echo()'s timecost
- [ ] refactor: organize test sets
- [ ] optimize: WaitingAnalyzer.analyzePrivatePhaseWaitingTiles // 500ms -> ?

- [ ] optimize: RoundTest
- [ ] optimize: RoundDrawTest
- [ ] optimize: WinAnalyzerTest

# round state class

responsibility

- support command by call $this->($commandName)($command.getParams())
- store state-specific fields such as RoundResult

# command system

client send command string
parse string into a command bind with a Game
command.execute()

publicCommandRoller
save commands as list

replay
initialState, command strings(notice NO random allowed)

## note: round logic

new phase

- reset and shuffle wall
- decide dealer player
- decide each player's wind

reset phase

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