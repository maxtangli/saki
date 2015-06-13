# saki
A japanese-mahjong solver.

## good practice

- Agile, TDD, KISS, bad-design WINs over-design

## bad smell

- [ ] ==,===,array_search => custom equalsTo() ?
- [ ] ugly const REGEX_XXX =>  class TileRegex ?
- [ ] static factory method such as fromString($s) force subclasses keep constructor signature => ?
- [x] ArrayObjectLike modify methods => protected methods in ArrayObjectLike

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

rush 6 exhaustive draw

- [ ] dead wall
- [ ] after command execute

rush round flow: public phase

- [ ] PublicPhase

rush round command

- [ ] Draw
- [ ] DiscardDrawn, when drawn one exists
- [ ] KeepDrawnAndDiscardOnHand, when drawn one exists
- [ ] DiscardOnHand, when no drawn one exists

- [ ] RonWin 0+1
- [ ] ZimoWin 0+0
- [ ] Pass

- [ ] Chow 2+1
- [ ] ExposedPon 2+1
- [ ] ExposedKong 3+1

- [ ] ConcealedKong 4+0

- [ ] PlusKong(with other's tile) 0+1
- [ ] PlusKong(with self's on hand tile) 1+0
- [ ] PlusKong(with self's candidate tile) 0+0

rush round draw

rush yaku

- [ ] Pinfu
- [ ] Menqingzimo
- [ ] Yipai

rush fushu

rush point

rush dora

rush red dora

rush player wind

- [ ] Changfeng
- [ ] Zifeng
- [ ] Yaku

rush next round & game over

advance features

- multi-media
- player AI
- replay
- player statistics

more advance features

- tenhou client AI
- chating AI
- skill mode
- training mode

## note

ObjectLikeArray

- loop/count/offsetGet
- all convenient modify methods, not exposed to client

Command

- RoundCommand operates on round, Round do not know Concrete Command? thus adding new Command will be easy?
- CommandAnalyzer list all possible commands with a given Round.

Command serialize

- toString discard p1 4p
- fromString DiscardCommand $round $player $params

## note: round logic

1. new phase

- [x] reset and shuffle wall, decide dealer player

2. init phase

- [x] each player draw 4*4 tiles
- [x] dealer player draw 1 candidate tile
- [x] go to dealer player's private phase

3. player's turn: private phase

- [x] only current player has candidate commands.
- [ ] if command is WinOnSelfCommand, go to round-win phase.

4. player's turn: public phase

- [ ] all players except current one may have candidate commands.
- [ ] only the highest-priority-command will be executed. If so, go to command-owner's turn.
- [ ] if command is WinOnOtherCommand, go to round-win phase.

5. round-win phase

- [ ] calculate fushu and Yaku, which decides point
- [ ] modify each player's point
- [ ] go to next round's new phase

detailed: list candidate commands in private phase

suppose drawn exists

- always: Discard
- special: