# saki

A japanese-mahjong server.

## good practice

goal

- good learning: reinvent all wheels without reference to exist algorithm and libraries, 
though reuse wheels is an important ability that should be mastered via other projects.

before coding

- time management: todo list, time-input statistics.
- requirement control: kiss, important first.
- it should be easy: feeling hard means abnormal, ex.x unclear requirement? bad design? 

coding

- agile development: incremental + kiss + tdd + refactor + design. e.x. notImplementedException.
- three and out design: if bad smells come at 3rd times, redesign by refactoring. 
- test as code: kiss, refactor, design. e.x. dataProvider, customAssert, TestUtils. ng. copy-paste everywhere.
- performance: avoid early optimization, optimize when tests are slow, use a profiler.

naming conventions

- constructor(): new members and set to default state
- init(): set members to default state, without constructor()'s new operations cost.
- reset($params): set members to specified state defined by $params and current members state.

## rush statistics

rush       | hours
--------- | -----
rush  0-5  | 34h
rush  6-10 | 23.8h
rush 11-15 | 15.8h
rush 16-20 | 25.6h
rush 21-25 | 26.7h
rush 26-30 | 24.1h
rush 31-35 | 17.4h
rush 36-40 | 24.6h
rush 41-45 | 15.3h
rush 46-50 | ing
     total | 207.3h + ing

## rush history

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

rush 30 refactor for joy 1.1h

- [x] refactor: Saki/Tile, Saki/Util 0.8h
- [x] Factory 0.3h

rush 31 benchmark 1.4h

- [x] enhance Timer 0.2h
- [x] Benchmark 0.3h
- [x] some common items 0.8h
- [x] log 0.1h

rush 32 refactor RoundTest 0.6h

- [x] optimize: remove unnecessary setUp() 0.3h // 1300ms -> 1160ms, -140ms
- [x] optimize: merge testToNextRound into testWinBySelf by avoid 1 winBySelf op // 1160ms -> 1020ms, -140s
- [x] measure testExhaustiveDraw 0.2h // it's ok
- [x] optimize: split slow parts into RoundWinTest 0.1h // conclusion: It's all WinAnalyzer's fault!

rush 33 refactor handTileList -> 13style + targetTile 3.7h

- [x] plan 0.2h // should have been considered before. Now it's too complicated to refactor.
- [x] refactor: reset() vs init() 0.3h // conclusion: reset() is more expressive.
- [x] refactor: ArrayLikeObject chain style. 0.5h
- [x] refactor: TileArea->getHandTileSortedList() -> getPrivateHand/getPublicHand 2.2h // refactor TileArea is hard, while refactor TileAreas is easy.
- [x] HandCount 0.5h

rush 34 optimize WinAnalyzer 6.3h

- [x] understand WaitingAnalyzer 0.4h

- [x] measure analyzeTarget 0.5h // point: slow functions should be tracked when added, which avoid future measure 
- [x] refactor, review 0.3h

- [x] measure analyzeSubTarget 0.4h
- [x] measure Yaku.existIn 0.3h
- [x] optimize GreenValueTilesYaku 0.6h // astonished! it's assertPrivatePhaseCompleteCount() that is slow (0.6ms) where wrongly use of TileSortedList

- [x] optimize TileSortedList 1.4h // 0.9ms -> 0.5ms/0.2ms by removing unnecessary process. point: slower because of violation of KISS
- [x] fix wrong impl of MeldList.toTileSortedList() 0.1h // astonished! all-tests 2.6s -> 1.2s by fixing MeldList.toTileSortedList() impl from TileList.insert() * n to TileList.toReducedValue().
- [x] remove unnecessary usage of TileSortedList: first try 0.6h

- [x] measure waitingAnalyzer 0.2h
- [x] measure and try optimize meldCompositionAnalyzer 1.5h // failed to optimize 8ms

- [x] summary: WinAnalyzer 70ms -> 20ms, all tests 2.6s -> 1.2s. a success! mainly reason: TileSortedList too slow.

rush 35 refactor YakuTestData -> RoundData.debugInit 5.4h

- [x] RoundWindTurn 0.3h
- [x] RoundDebugResetData 0.3h
- [x] refactor: remove MockRound.debugSetTurn() -> Round.debugSkipTo 0.4h
- [x] MockRound.debugSkipTo() 1h

- [x] <del>goal: remove YakuTestData</del>
- [x] recall where am I a month ago 0.5h
- [x] refactor: YakuTestData -> RoundData.debugInit() 0.8h

- [x] goal: remove MockRound
- [x] TileAreas.debugSet() -> TileAreas.debugSetPrivate/Public() 0.3h
- [x] refactor: move MockRound methods into RoundData members 1.9 h

- [x] summary: how to avoid those terrible refactorings? refactor as soon as bad smell appears; self code review periodically.

rush 36 optimize tests: => 1s 2.3h

- [x] optimize: new Round().drawInit 1.8h // ArrayLikeObject.pop() 5ms -> 1ms by simplify, Wall().draw 66ms -> 3ms by fix wrongly use of TileSortedList
- [x] optimize: new RoundData() 0.5h // TileSet extends TileSortedList -> extends TileList
- [x] bug: Timer not accurate since echo()'s time cost

rush 37 introduce commands: first step 7.5h

- [x] scratch 3.2h // point: confusion in design or coding comes from lack of REQUIREMENT ANALYSIS!
- [x] parse by ParamDeclaration 1h // point: explore requirement by trying design and coding
- [x] test parse 0.2h
- [x] try profiling 1.2h
- [x] optimize: remove TileSortedList in TileAreas 0.2h // profiler found out the ultimate evil thing in pj saki!
- [x] CommandParser 0.1h

- [x] Command.executable() 1h

- [x] refactor and recall where am i 0.4h
- [x] refactor: replace Symfony autoloader by Composer ones 0.2h

rush 38 add KingTileWinYaku 1.4h

- [x] analyze 0.8h
- [x] KingTileWinYaku 0.6h

rush 39 introduce PhaseState 5.3h

- [x] analyze: phase logic 2.3h // !important: requirement first, design second, coding last.
- [x] refactor: move RoundData.toXXPhase() into PhaseState 0.7h
- [x] refactor: move TurnManager's roundPhase methods into PhaseState 0.6h
- [x] refactor: handle PrivatePhaseState.isFromInit 0.1h
- [x] refactor: move RoundData's game-over logic into OverPhaseState 0.4h
- [x] refactor: move Round.passPublicPhase() into PublicPhaseState 0.2h
- [x] refactor: move Round.handleFourKongDraw() into PublicPhaseState 1h

rush 40 introduce commands: second step 8.1h

- [x] refactor: move Round methods into RoundData except commands 1.1h

- [x] review: Command system 0.5h
- [x] refactor: move into Commands - private 0.9h
- [x] refactor: move into Commands - public 0.6h
- [x] refactor: rename Kong concerned commands
- [x] refactor: move into Commands - passPublic() 0.2h

- [x] CommandProcessor 0.4h
- [x] MockHandCommand 0.5h
- [x] advanced TileParamDeclaration 1.3h // agile: not do until currently actual required!
- [x] refactor: try string-style-commands in tests 0.4h

- [x] I option for SelfWindParamDeclaration 0.2h
- [x] refactor: move into Commands - debug 2h

rush 41 merge Round and RoundData together 1.3h

- [x] modify RoundData init logic: forward to private 0.5h
- [x] test

- [x] add same name methods into RoundData
- [x] replace 'new Round()' by 'new RoundData()' 0.4h
- [x] test
- [x] remove not used Round class

- [x] rename RoundData -> Round 0.1h
- [x] replace 'roundData'->'round', 'rd'->'r' 0.1h
- [x] test

- [x] refactor: remove getCurrentPlayer(), getRoundPhase() 0.2h

rush 42 add RobbingAQuadYaku 5.1h

- [x] research: kong concerned rule 0.6h // smallKong not exist in Japanese Mahjong!
- [x] refactor: move kong test cases into KongConcernedTest, etc 0.3h
- [x] test: full cases of FourKongDraw 0.6h
- [x] RobbingAQuadPhase 0.4h

- [x] analyze 0.1h
- [x] robAQuadPhase: winByOtherCommand only 0.1h

- [x] robAQuadPhase: target tile, robAQuadYaku 0.5h
- [x] test: targetTile,robAQuad

- [x] refactor: FuritenTileList 0.6h
- [x] test: furiten 1.9h

rush 43 dora yaku 5h

- [x] design 0.3h
- [x] doraFacade 1.3h
- [x] test doraFacade 0.5h

- [x] refactor Yaku 0.2h
- [x] refactor YakuItemList 1h
- [x] adapt yakuAnalyzer 1h
- [x] DoraYaku 0.6h
- [x] UraDoraYaku 0.1h

rush 44 red dora 1.7h

- [x] Tile.getInstance() 0.3h
- [x] Tile.isRed() 0.2h
- [x] Tile.getID() 0.3h

- [x] Tile.fromString(), regex 0.2h
- [x] TileList.fromString() 0.2h
- [x] RedDoraYaku 0.2h

- [x] Tile.toString() 0m,0p,0s: 0.1h
- [x] TileList.toString()
- [x] TileList sort: 1234056789m 0.2h

rush 45 refactor: remove TileSortedList 1.7h

- [x] MeldType.valid 0.5h

- [x] refactor MeldTypeTest 0.3h
- [x] summary 0.2h
- [x] WeakMeldType.getWaitingTiles 0.4h

- [x] furuteWaiting 
- [x] tileSeries 0.1h

- [x] remove 0.2h

rush 46 refactor ArrayList 7.6h

- [x] scratch Linq-style functions 0.6h
- [x] introduce phpdoc: static 0.2h // see how foolish I was all these times!
- [x] adapt getAggregated 0.2h
- [x] adapt all,any

- [x] remove toArray.select, toFilteredArray -> fromSelected 1.5h
- [x] remove needless hook 0.1h
- [x] remove boring Benchmark

- [x] adapt distinct 0.2h
- [x] add getCounts 0.3h
- [x] remove getEqualValueCount 0.2h

- [x] adapt orderBy 0.4h

- [x] adapt getMin,getMax 0.3h
- [x] trait Comparable 0.7h
- [x] adapt concat 0.1h
- [x] remove walk, etc. 0.1h
- [x] adapt shiftLeft 0.1h
- [x] remove toEquals 0.1h
- [x] adapt fromArray 0.1h
- [x] adapt remove etc 0.3h
- [x] adapt insert etc 0.1h
- [x] adapt replace etc 0.2h 
- [x] adapt getIndex,getValueAt etc 0.8h
- [x] format 1h

rush 47 refactor Saki/Tile/ 4.4h

- [x] refactor Tile 0.9h
- [x] refactor Tile.ID 1.5h // WARNING: be careful of over-refactoring!
- [x] TileFactory 0.5h

- [x] refactor TileList 0.8h
- [x] refactor TileSet 0.1h
- [x] optimize: by profiler 0.6h

rush refactor Saki/Meld/

- [x] MeldType 1.2h
- [x] Meld 1.2h
- [ ] MeldList

rush red dora: meld issue

- [ ] adapt discard/createMeld logic

rush public command roller

- [ ] public command roller

rush rule.md doc

- [ ] rule 0.6h
- [ ] tile 0.6h
- [ ] yaku 1.2h
- [ ] furiten 0.8h

rush command candidates

- [ ] XXXCommand::getExecutables($player) // maybe better to exist in AI class?

rush all yaku: HeavenlyWin, EarthlyWin, HumanlyWin

- [ ] fix YakuTest where Triple declared
- [ ] fix FinalTileWin Fish/Moon 0.1h // ? what's wrong
- [ ] HeavenlyWin
- [ ] EarthlyWin
- [ ] HumanlyWin

rush all yaku: thirteen orphans

- [ ] thirteen orphan meld

rush refactor

- [ ] remove redDora tricks
- [ ] Hand = HandTileList + DeclareMeldList
- [ ] fix: mockHand target tile vs robQuadPhase target tile
- [ ] refactor: TileSeries <-> MeldList.xxx, remove needless TileSeries
- [ ] refactor: RoundResult
- [ ] refactor: move DrawScore logic into separate class
- [ ] refactor: simplify reset(),debugReset(),toNextPhase() 0.2h

rush optimize tests

- [ ] optimize: WaitingAnalyzer.analyzePrivate // 270ms
- [ ] optimize: WinAnalyzerTest // 230ms
- [ ] optimize: YakuTest 0.1h // 120ms  1 test case 40ms -> debugReplaceHand() slow?
- [ ] optimize: RoundWinTest 80ms
- [ ] optimize: RoundDrawTest/testFourReachDraw // 40ms