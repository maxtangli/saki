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


rush 5 round 6/5 2.5h 6/7 1.5h 6/9 1.0h
- [x] Wall
- [x] TurnManager
- [x] new Round
- [ ] Round flow
- [ ] simple UI: draw and discard

to be scheduled
- [ ] exposed/concealed triplet/kong

## note - ObjectLikeArray

loop/count/offsetGet
all convenient modify methods, not exposed to client


## note - round flow

round init

getCandidateCommands. command.priority

send candidate commands to each player

wait for each player decide command
get a command
 if decidedCommand.priority is biggest || not exist other player undecided
  go to next process
 else
  keep wait

command.execute($round): modify round tiles

if gameover: no more drawable or one win
 send result
 new next round
else
 goto get commands