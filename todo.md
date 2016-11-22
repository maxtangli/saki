## rush statistics

rush       | hours
---------- | -----
rush  0- 5 | 34.0h
rush  6-10 | 23.8h
rush 11-15 | 15.8h
rush 16-20 | 25.6h
rush 21-25 | 26.7h
rush 26-30 | 24.1h
rush 31-35 | 17.4h
rush 36-40 | 24.6h
rush 41-45 | 15.3h
rush 46-50 | 24.0h
rush 51-55 | 21.5h
rush 56-60 | 33.0h
rush 61-65 | 17.1h
rush 66-70 | 22.9h
rush 71-75 | 15.7h
rush 76-80 | 23.4h
rush 81-85 | 28.2h
rush 86-90 | 11.9h
rush 91-95 | 13.3h
rush 96-100| ing
     total |425.3h + ing

## rush history

rush 0 scribble 1.5h

- [x] terminals in english
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
- [x] Phase
- [x] PrivatePhase: getCandidateCommands()

- [x] simple UI

- [x] PrivatePhase: execute command and to next turn
- [x] RoundCommand toString()/fromString()
- [x] bug fix: InfiniteIterator buggy with session
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
- [x] bug fix: turn calculate 1.0h

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
- [x] refactor public phase chow/pung/kong 0.3h
- [x] private phase: kong, extendKong 0.7h

rush 10 first yaku impl: all runs yaku 7.8h

- [x] pseudocode 3.5h
- [x] yaku.xls 1.5h
- [x] refactor: rename terminals to follow yaku names 0.5h
- [x] organize code 1.0h
- [x] is4WinSetAnd1Pair() 0.2h
- [x] allRunsYaku 1.1h

rush 11 more yaku 2.2h

- [x] RiichiYaku 0.7h
- [x] WinState 0.7h
- [x] ValueTilesYaku 0.6h
- [x] AllSimplesYaku
- [x] test 0.2h

rush 12 win flow 5.8h

- [x] analyze target 0.7h
- [x] use-case analyze 2.2h

- [x] remove TurnManager into PlayerList 0.5h
- [x] bug fix: not drawn. cause; PlayerList sharable -> unsharable 0.7h
- [x] adapt Round with PlayerList 0.3h

- [x] private phase: win on self 0.8h
- [x] over phase: exhaustive drawn 0.6h

rush 13 point table 4.6h

- [x] point table 3.3h
- [x] analyze 0.6h
- [x] refactor PlayerList 0.4h
- [x] bug fix: Result.getOriginPoint() 0.3h

rush 14 next round 1.4h

- [x] RoundData 0.3h
- [x] reach condition 0.2h
- [x] PlayerList.reset() 0.2h
- [x] refactor: move $wall into RoundData 0.1h
- [x] analyzerTarget constructor: add $roundData 0.1h
- [x] toNextRound() 0.2h
- [x] Result->getNextPlayer() 0.3h

rush 15 game over 1.8h

- [x] refactor: 0.4h
- [x] finalPoint 1h
- [x] result: winner order, final point 0.4h

rush 16 game over fix 2.1h

- [x] isLastRound() 0.6h
- [x] refactor: move PlayerList into RoundData 1.1h
- [x] bug fix: PlayerList 0.4h

rush 17 win issues 1.6h

- [x] reach point
- [x] round n point 0.3h
- [x] game over: minus point 0.1h
- [x] WinResult 0.5h
- [x] public phase: win on other
- [x] public phase: multiple win on other 0.4h
- [x] test design 0.3h

rush 18 refactor 5h

- [x] refactor: point strategy 2h
- [x] refactor: Areas 0.5h
- [x] refactor: PrevailingWindData 1.5h
- [x] refactor: accumulatedRiichiCount, remove unnecessary methods in Round&RoundData. 0.8h
- [x] bug fix: getTopPlayer() is wrong when same point top players exist 0.2h

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
- [x] refactor SeriesType: 2.1h

rush 22 fix getMeldCompositions() 1.5h

- [x] bug fix: getMeldCompositions() won't count for 112233s like tiles 1.3h
- [x] bug fix: MeldCompositionAnalyzer: exposed 0.2h

rush 23 WinAnalyzer: furiten 14.7h

- [x] analyze waiting and furiten 2.2h
- [x] Series.getWaitingTiles 1.3h // unnecessary?
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

rush 24 Ron, MultipleRon 1h

- [x] round.ron 0.2h
- [x] test MultipleRon 0.3h
- [x] refactor: remove WinResult subclasses 0.3h
- [x] test Round.winByXXX 0.2h

rush 25 AbortiveDrawResult 1.5h

- [x] add ResultType 0.4h
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
- [x] bug fix: Meld.isXXXWinSet() 0.4h
- [x] bug fix: FullStraight 1h // Meld.equals issues; forget to write return for
- [x] test fan3,fan6 yakus 0.2h
- [x] test yakuman/yakuman2 yakus 0.3h

rush 27 refactor: concealed 1.7h

- [x] refactor: remove isExposed, use isConcealed instead 1.7h
- [x] Meld
- [x] MeldList
- [x] Area.declareMeld
- [x] Yaku, YakuList
- [x] MeldCompositionAnalyzer
- [x] YakuAnalyzer
- [x] grep exposed

rush 28 refactor for beauty 5.9h

- [x] refactor: move Round.Result into RoundData 0.2h
- [x] refactor: organize WinTarget 0.2h
- [x] refactor: organize Areas, PlayerArea 0.2h

- [x] TurnManager 0.7h
- [x] Roller 1.4h
- [x] refactor: move RoundData.$result into $turnManager 0.1h
- [x] refactor: move RoundData.$phase into $turnManager 0.2h
- [x] refactor: move RoundData.$playerList's rolling role into $turnManager 1.2h

- [x] refactor: move Area.init() into Areas 0.3h
- [x] refactor: for ArrayLikeObject: test more, add writable, refactor valueToIndex series 1.4h

rush 29 all yaku: reach concerned 2.8h

- [x] refactor: AbstractValueTilesYaku 0.2h
- [x] ClaimHistory 0.3h
- [x] Turn 0.4h
- [x] YakuSet 0.6h // batch is not necessary and too much labour here
- [x] test reach, doubleRiichi 0.4h
- [x] bug fix: wrongly turn into over phase after pass N's public phase 0.1h // It's not a bug but test case results in FourWindDraw!
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
- [x] optimize: merge testToNextRound into testTsumo by avoid 1 tsumo op // 1160ms -> 1020ms, -140s
- [x] measure testExhaustiveDraw 0.2h // it's ok
- [x] optimize: split slow parts into RoundWinTest 0.1h // conclusion: It's all WinAnalyzer's fault!

rush 33 refactor handTileList -> 13style + targetTile 3.7h

- [x] plan 0.2h // should have been considered before. Now it's too complicated to refactor.
- [x] refactor: reset() vs init() 0.3h // conclusion: reset() is more expressive.
- [x] refactor: ArrayLikeObject chain style. 0.5h
- [x] refactor: Area->getHandTileSortedList() -> getPrivateHand/getPublicHand 2.2h // refactor Area is hard, while refactor Areas is easy.
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

- [x] PrevailingWindTurn 0.3h
- [x] RoundDebugResetData 0.3h
- [x] refactor: remove MockRound.debugSetTurn() -> Round.debugSkipTo 0.4h
- [x] MockRound.debugSkipTo() 1h

- [x] <del>goal: remove YakuTestData</del>
- [x] recall where am I a month ago 0.5h
- [x] refactor: YakuTestData -> RoundData.debugInit() 0.8h

- [x] goal: remove MockRound
- [x] Areas.debugSet() -> Areas.debugSetPrivate/Public() 0.3h
- [x] refactor: move MockRound methods into RoundData members 1.9 h

- [x] summary: how to avoid those terrible refactorings? refactor as soon as bad smell appears; self code review periodically.

rush 36 optimize tests: => 1s 2.3h

- [x] optimize: new Round().drawInit 1.8h // ArrayLikeObject.pop() 5ms -> 1ms by simplify, Wall().draw 66ms -> 3ms by fix wrongly use of TileSortedList
- [x] optimize: new RoundData() 0.5h // TileSet extends TileSortedList -> extends TileList
- [x] bug fix: Timer not accurate since echo()'s time cost

rush 37 introduce commands: first step 7.5h

- [x] scratch 3.2h // point: confusion in design or coding comes from lack of REQUIREMENT ANALYSIS!
- [x] parse by ParamDeclaration 1h // point: explore requirement by trying design and coding
- [x] test parse 0.2h
- [x] try profiling 1.2h
- [x] optimize: remove TileSortedList in Areas 0.2h // profiler found out the ultimate evil thing in pj saki!
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
- [x] refactor: move TurnManager's phase methods into PhaseState 0.6h
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

- [x] I option for SeatWindParamDeclaration 0.2h
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

- [x] refactor: remove getCurrentPlayer(), getPhase() 0.2h

rush 42 add RobbingAQuadYaku 5.1h

- [x] research: kong concerned rule 0.6h // smallKong not exist in Japanese Mahjong!
- [x] refactor: move kong test cases into KongConcernedTest, etc 0.3h
- [x] test: full cases of FourKongDraw 0.6h
- [x] RobbingAQuadPhase 0.4h

- [x] analyze 0.1h
- [x] robAQuadPhase: ronCommand only 0.1h

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

- [x] Tile.create() 0.3h
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
- [x] series 0.1h

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

rush 48 refactor Saki/Meld/ 7.1h

- [x] MeldType 1.2h
- [x] Meld 1.2h
- [x] MeldList 2.6h
- [x] MeldTypeFacade 0.1h
- [x] MeldTypeAnalyzer 0.2h
- [x] MeldCompositionsAnalyzer 1.8h

rush 49 all yaku: thirteen orphans 1.8h

- [x] thirteen orphan meld 1.1h
- [x] test 0.7h

rush 50 refactor Saki/Win/ part1 2.1h

- [x] remove needless XXXSeries 0.3h
- [x] Series 0.4h
- [x] SeriesAnalyzer 0.3h
- [x] WinAnalyzer 1.1h

rush 51 all yaku: HeavenlyWin, EarthlyWin, HumanlyWin 0.9h

- [x] fix YakuTest where concealed Triple declared 0.1h
- [x] HeavenlyWin, EarthlyWin, HumanlyWin 0.6h
- [x] test NineGatesYaku 0.2h

rush 52 refactor Area: introduce Hand 12.5h // a terrible trip ...

- [x] add Hand, SubHand 0.6h
- [x] adapt Hand into Area: partly 0.6h

- [x] refactor Player 0.2h
- [x] add targetTileCallback for Area 0.2h
- [x] ArrayList.readonly 0.6h
- [x] move Areas.getXXHand into Area 0.8h

- [x] introduce NullObject pattern for TargetData 0.7h
1. add null object support: for TargetData
2. replace === null logic by null object
3. inline method: hasTargetData() // main benefit
- [x] Area.getTarget ? 1.2h // terrible ...

- [x] extract class: RiichiStatus 0.1h
- [x] lock discard 0.1h

- [x] add TargetData.TargetType 0.3h
- [x] move Area.command logic into Areas 0.8h
- [x] remove Hand.orderBy 0.2h
- [x] Change logic: Area.handTileList -> Area.public 3.7h // terrible ...
- [x] remove Area.handTileList 2.1h // terrible ...
- [x] etc 0.2h

rush 53 refactor SeatWind 2.6h

- [x] extract class: SeatWind 0.5h
- [x] add SeatWind into Area 1h // terrible ... recall beginning mind!
- [x] adapt SeatWind 0.3h

- [x] adapt Target 0.2h
- [x] adapt ClaimHistory 0.3h
- [x] adapt Turn 0.1h
- [x] adapt OpenHistory 0.2h

rush 54 refactor reach concerned 3.8h

- [x] adapt RiichiStatus 0.3h
- [x] adapt ClaimHistory 0.1h
- [x] refactor ClaimHistory 0.4h // my most satisfied class!

- [x] refactor OpenHistory 0.8h
- [x] rewrite OpenHistory 1.7h
- [x] refactor OpenHistory 0.5h

rush 55 refactor Saki/Win/ part2 1.7h

- [x] MeldCompositionsAnalyzer 0.2h

- [x] isFuriten 0.7h
- [x] fix turnFuriten bug 0.5h

- [x] SeriesAnalyzer, Yaku 0.3h

rush 56 refactor Turn concerned: part1 2.3h

- [x] refactor Turn 1h // keep agile! keep clear goal when design!

- [x] add PrevailingWind 0.5h
- [x] refactor GameTurn 0.4h
- [x] refactor Round a little 0.4h

rush 57 move Player.point, TurnManger into Areas 4.6h

- [x] know terminals and rename 1h
- [x] move Player.point into Area 0.8h
- [x] refactor Areas.reachPoints 0.2h
- [x] refactor Areas.recordOpen 0.2h
- [x] add Areas.getArea 0.2h

- [x] try adapt DiscardCommand 1h
- [x] remove TurnManger 1.2h // with a self-managed Turn abstraction, TurnManager no longer required!

rush 58 refactor Turn concerned: part2 7.6h

- [x] add ComparableIndex 0.2h
- [x] refactor Tile.getWindOffsetFrom/To 0.3h
- [x] refactor PrevailingWindManager 1.7h
- [x] refactor PrevailingWindManager.debugSet 1h

- [x] refactor OverPhaseState.isGameOver 0.5h
- [x] adapt PrevailingStatus 1.5h

- [x] move SeatWindTurn into Area 0.4h
- [x] add PrevailingContext 1.8h

- [x] refactor Util/ 0.2h

rush 59 refactor remove Player usage 7.4h

- [x] failed refactor 0.5h
- [x] remove Areas Command usage 0.7h
- [x] remove phase usage 0.3h
- [x] remove Areas debugSet usage 0.5h
- [x] remove WinTarget usage 0.6h
- [x] remove Areas other usage 1.6h

- [x] remove PlayerList.getXXXWind 1.1h
- [x] add PointList 1.4h
- [x] refactor Command/ 0.7h

rush 60 refactor Result 10.1h

- [x] rename 0.2h

- [x] review Point/ 0.5h // it appears to be well designed since no modification required after 6 months.

- [x] review Result 0.4h
- [x] refactor ResultTest 0.4h

- [x] rewrite ExhaustiveDrawResult 1.1h
- [x] rewrite AbortiveDrawResult 0.1h
- [x] rewrite WinResult: part1 1.1h // require more direct design

- [x] analyze point calculate 1.5h
- [x] rewrite PointTableItem 0.5h // so happy to write clever code!
- [x] test PointTableItem 0.6h // so happy to write clever test!

- [x] rewrite WinResult: part2 1.2h
- [x] bug fix: failed to consider ceil issues when Tsumo! 0.5h// so terrible ...
- [x] introduce terminals: tsumo, ron.
- [x] test and refactor WinResult 1.1h

- [x] adapt WinResult 0.8h
- [x] review and refactor 0.1h

rush 61 refactor Draw/ 1.7h

- [x] add Draw 0.4h
- [x] add DrawAnalyzer 0.3h
- [x] review and refactor 1h

rush 62 refactor Win/ 3.5h

- [x] refactor WaitingAnalyzer 2.1h
- [x] refactor SeriesAnalyzer 0.3h
- [x] refactor Series 0.8h

- [x] refactor Win/Waiting 0.3h

rush 63 rewrite Win/Score 2.3h

- [x] ScoreStrategy // 1.3h
- [x] test // 0.7h
- [x] adapt ScoreStrategy 0.3h

rush 64 refactor Areas 3.4h

- [x] etc 0.5h
- [x] remove Round.debugSkipTo 0.2h

- [x] refactor RobbingPublicPhaseState 1.5h

- [x] introduce Claim 1.2h

rush 65 refactor Areas 6.2h

- [x] introduce TargetHolder 0.5h
- [x] move Areas.debugSetXXX into Area 0.5h
- [x] Area.resetImpl 0.1h
- [x] merge Area.debugSet 1.2h

- [x] add Command error message 0.1h
- [x] wall.deal 0.2h

- [x] refactor TargetHolder 0.4h
- [x] merge openHistory, Discard 1.5h

- [x] move Areas logic into Area 1.2h
- [x] clean Area operations 0.5h

rush 66 refactor Areas 9.1h

- [x] analyze SetTarget 1h
- [x] move Public.postLeave into Private 0.1h
- [x] simplify Public, RobPublic, FourKongDraw 0.3h

- [x] refactor openHistory 0.5h
- [x] refactor Area.claim 1h
- [x] bug fix: recordClaim in private phase used wrong Turn // 0.5h
- [x] introduce Open 0.4h
- [x] merge claim operations 1.9h

- [x] introduce HandHolder 1.4h
- [x] introduce RiichiHolder 0.7h
- [x] refactor etc. 1.1h
- [x] refactor IndicatorWind 0.2h

rush 67 refactor etc 4h

- [x] introduce PointHolder 0.7h
- [x] refactor etc. 0.5h

- [x] clean Target flow 1.1h
- [x] test Claim,Open 0.8h

- [x] add Indicators handling and test 0.2h

- [x] refactor etc. 0.7h

rush 68 red dora: meld issue 2.9h

- [x] check deal, draw
- [x] adapt Discard, Riichi 0.6h
- [x] adapt Chow 0.3h
- [x] adapt Pung 0.4h

- [x] refactor kong command params 0.6h
- [x] adapt Kong
- [x] adapt ConcealedKong
- [x] adapt ExtendKong 0.5h
- [x] refactor RoundTest 0.5h

rush 69 refactor Command/ 4.4h

- [x] refactor: CommandParser 1.2h
- [x] refactor: remove CommandContext.bindActor 0.6h
- [x] refactor: clean command classes 2.1h
- [x] refactor: remove SeatWind:I usage
- [x] refactor: remove Tile:s- usage 0.3h
- [x] refactor: CommandParser.parseLines 0.2h

rush 70 refactor Round members 2.5h

- [x] refactor: move prevailingCurrent into Areas 0.2h
- [x] refactor: remove WinTarget's Round usage 0.2h
- [x] refactor: remove Round.getXXX 0.6h

- [x] refactor: merge Areas into Round 0.8h
- [x] refactor: remove CommandContext 0.7h

rush 71 CommandProvider 2.3h

- [x] CommandProvider 1.7h
- [x] Discard
- [x] refactor: PlayerCommand constructor 0.6h

rush 72 waitingAnalyzer bug 2.5h

- [x] bug fix: waitingAnalyzer for special TileList 0.7h
- [x] waitingAnalyzer: thirteen orphan case 0.4h
- [x] WeakThirteenOrphanMeld 1.1h
- [x] speed up WeakThirteenOrphanMeld 0.2h
- [x] clean MeldType 0.2h

rush 73 command candidates: impl 4.5h

- [x] refactor: getExecutableList 0.3h
- [x] Riichi 0.1h
- [x] NineNineDraw 0.9h
- [x] Tsumo 0.2h
- [x] ConcealedKong 1h
- [x] ExtendKong 0.4h

- [x] Ron 0.3h
- [x] Chow 0.5h
- [x] Pung 0.7h
- [x] Kong 0.1h

- [x] abstract declaration

rush 74 refactor claim Commands 2.6h

- [x] plan refactor 0.3h
- [x] refactor: chow,pung,kong,concealedKong to TileList by regex 0.3h
- [x] refactor: introduce base class - PublicClaimCommand 0.2h

- [x] refactor: simplify getExecutableListImpl 1.3h
- [x] refactor: remove silly assignment etc. 0.5h

rush 75 profiler waitingAnalyzer 3.8h

- [x] profiler 0.5h

Tile.toFormatString
Tile.create
TileList.getIndex
TileFactory.genOrGenerate
TileType.isSuit 40000+times?
ArrayList->fromArray
Tile.valid
TileFactory.toValueID
ArrayList.construct
top35%

- [x] simplify Tile 2.3h

TileList.getIndex
ArrayList.fromArray
ArrayList.construct
TileList.toString
Tile.toFormatString
array_map
ArrayList.indexExist
Tile.toString
top35%

- [x] simplify ArrayList etc. 1h

ArrayList.getIndex
ArrayList.construct
array_map
* analyzeMeldTypeImpl
* getPossibleCuts
array_filter
getCyclicNext
Tlle.getSuitList.$toSuit
ArrayList.util_boxing
top35%

rush 76 optimize WaitingAnalyzer: step1 2h

- [x] code view: MeldListAnalyzer 0.2h
- [x] optimize,refactor ArrayList etc 1.8h

rush 77 etc 4.6h

- [x] PublicCommandBuffer 0.6h
- [x] refactor: etc 0.9h

- [x] simulator ing: 1.3h

- [x] debug command: init, toNext
- [x] bug fix: kong command should be invalid if not drawReplacementAble 0.6h
- [x] bug fix: chow condition of next seatWind 0.3h
- [x] bug fix: discard condition when riichi 0.2h
- [x] add WinReport for WinResult 0.4h
- [x] bug fix: target should exist in OverPhase 0.2h

- [x] refactor: PlayerCommand.matchPhase, matchActor should be static 0.1h

rush 78 web demo 8.8h

- [x] import Racket+js 0.5h

- [x] server code 1.9h
- [x] Round.toJson 0.5h
- [x] html view demo 5.9h

rush 79 refactor and complete yaku tests 4.8h

- [x] refactor DoraYaku tests 0.5h
- [x] refactor RiichiYaku tests 0.4h
- [x] refactor FirstTurnWin etc tests 0.2h
- [x] test BlessingOfHeaven etc 0.4h
- [x] fix BottomOfTheSea 1.1h

- [x] add SkipToCommand 0.5h

- [x] refactor YakuTestData 0.4h
- [x] fix pure yaku tests 0.4h
- [x] organize yaku tests 0.4h
- [x] remove YakuTestData 0.5h

rush 80 refactor tests etc. 3.2h

- [x] refactor tests 0.7h
- [x] refactor Win/Fu 0.6h
- [x] refactor WinTarget 0.5h
- [x] refactor Win/Yaku 0.3h
- [x] refactor Win/Yaku/XXX 0.5h
- [x] clear to-do-s 0.6h

rush 81 swap-calling 1.4h

- [x] search 0.1h
- [x] rule.swapCalling 0.1h
- [x] chow.executable 0.3h
- [x] test 0.2h
- [x] discard.executable 0.7h
- [x] test
- [x] refactor

rush 82 hosting 8h

- [x] setup AWS LAMP + websocket server 6.5h
- [x] test with my friend LS 0.5h
- [x] nohup php bin/server.php &
- access privilege for /var/www symbol link fies 1h

rush 83 UI: smart device UI, hand, tileSet 3.7h

- [x] prepare images 0.2h
- [x] search UI library, know jQuery Mobile 0.7h

- [x] button group 0.2h
- icon only button 1.2h
- [x] tile button: use text 0.1h
- [x] hand prototype 0.7h
- [x] tileSet 0.6h

rush 84 DemoUI areas 10.6h

- [x] plan contents 0.5h

- [x] html: standard semantic 1h
- [x] js: Saki.Game&View 2h

- [x] css: private, declare 2h
- [x] html&css: area 1h
- [x] js: areas 3.3h

- [x] css: dt, dd 0.5h
- [x] refactor: js 0.3h

rush 85 DemoUI round 4.5h

- [x] reference page: 0.8h
- [x] css: stack, wall 1.4h

- [x] refactor: DeadWall.getIndicators 0.6h
- [x] refactor: ArrayList 0.4h
- [x] json, js: deadWall 0.8h
- [x] hosting: update 0.5h

rush 86 refactor ArrayList 5.3h

- [x] optimize sort: replace comparator by sortKeySelector 1h // 10 times faster for tileSet sort, though not affect test cases
- [x] refactor getMin,getMax: remove Comparator 0.5h // not for optimization but for clean design
- [x] etc 1.5h
- [x] remove lock, unlock 0.1h

- [x] replace distinct.equal by compareKeySelector 0.6h
- [x] replace getIndex,remove.equal by compareKeySelector 0.5h
- [x] replace indexExist.equal, valueExist.equal by compareKeySelector 0.5h
- [x] simplify red tile compare 0.6h

rush 87 DemoUI areas renew 4.2h

- [x] css: areas renew 1.1h
- [x] css: areas rotation view 1h
- [x] css: areas tenhou view 1.9h
- [x] css: areas precise tenhou view 1.2h

rush 88 bug fix 2.1h

- [x] bug fix: thirteen orphan tsumo not works 0.2h
- [x] bug fix: executable commands after riichi

- [x] refactor: add error message for invalid command 1h
- [x] bug fix: invalid discard after chow 0.5h // Open.valid wrongly use $targetTile, which should be $tile
- [x] bug fix: assertPrivate($seatWind) not works 0.2h
- [x] bug fix: discardCommand.executable failed to consider swapCalling 0.2h
- [x] bug fix: riichiCommand candidate when already riichi

rush 89 DemoUI round 3.2h

- [x] css&js refactor: remove useless semantic html 0.7h
- [x] add log 0.5h // scroll bar failed...

- [x] css: actor 0.3h
- [x] css: round 1.5h // margin-left seems not a good center way
- [x] css: view width adapt screen 0.2h

rush 90 area relation 4.1h

- [x] analyze 0.9h
- [x] refactor: remove PlayerList, Player 0.2h
- [x] php,js: area.relation 1h

- [x] refactor: Play 0.4h
- [x] assign a unique wind to each client 1.6h

rush 91 conn role manage 3.7h

- [x] refactor: introduce Privilege to replace $viewer 0.7h
- [x] refactor: move xx.toJson logic into RoundSerializer 1h

- [x] refactor: introduce Play to wrap connection owned data 0.4h
- [x] refactor: introduce Participant 0.2h

- [x] RoleManager 0.7h
- [x] Viewer no see commands 0.3h

- [x] Viewer no see hand 0.5h
- [x] bug fix: conn not closed after browser tab closed 0.1h

rush 92 mock client 2.5h

- [x] analyze 0.4h
- [x] AIClient assign 0.1h

- [x] analyze 0.6h
- [x] refactor: ArrayList.toGroup 0.2h
- [x] view: conn info 0.9h

- [x] bug fix: over phase.draw view not handled 0.3h

rush 93 AI-lv0: random execute 1h

- [x] analyze 0.3h
- [x] AI execute random private 0.3h
- [x] AI execute random public 0.4h

rush 94 refactor Command 2.2h

- [x] refactor: DebugCommand 0.2h

- [x] analyze PlayerCommand 0.3h
- [x] refactor: merge matchPhase(), matchActor() into PlayerCommand 0.4h

- [x] analyze CommandProvider 0.2h
- [x] refactor: move getExecutableList() into PlayerCommand 0.2h
- [x] refactor: move getExecutableList() into impl class 0.2h
- [x] refactor: move getExecutableList() into Provider 0.2h

- [x] refactor: remove PlayerCommand.matchXXX() 0.3h

- [x] refactor: remove CommandArgumentException 0.2h

rush 95 integrate PublicCommandBuffer 3.9h

- [x] PublicCommandDecider 1.3h
- [x] add PassCommand 0.8h
- [x] test

- [x] integrate PublicCommand 1.4h
- [x] test 0.2h

- [x] adapt and test provider 0.2h

rush Wall view

- [x] Dice 0.2h

- [ ] php: player's wall view
- [ ] css: wall 0.5h

rush meld view

rush optimize WaitingAnalyzer: step2

- [ ] optimize analyzePublic
- [ ] optimize analyzePrivate

rush toward 100% rules!

- [ ] skip needless pass commands in PlayServer or Round
- [ ] game over rule confirm & tests
- [ ] multiple ron
- [ ] 流局満貫
- [ ] 責任払い

rush large scale refactoring

- [ ] introduce Validator 1.1h
- [ ] red dora design
- [ ] Open, Claim design
- [ ] toNextPhase design

rush room

- [ ] analyze 0.7h

- [ ] register: email, nickname, password.
- [ ] login: allow at most 1 websocket connection.
- [ ] logout:?

refactor process

module      | process
----------- | -----
Command     | ok
Game        | ok
Meld        | ok
Phase       | ok
Tile        | ok
Util        | ok
Win         | ok
Win/Draw    | ok
Win/Fu      | ok
Win/Point   | ok
Win/Result  | ok
Win/Score   | ok
Win/Series  | ok
Win/Waiting | ok
Win/Yaku    | ok
Win/Yaku/XX | ok

rush rule.md doc

- [ ] rule 0.6h
- [ ] tile 0.6h
- [ ] command 0.6h
- [ ] yaku 2.8h
- [ ] furiten 0.8h
- [ ] demo 1.3h