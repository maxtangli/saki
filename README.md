# saki
A japanese-mahjong solver.

## good practice

- Agile, TDD, KISS, bad-design WINs over-design

## bad smell

- [ ] ==,===,array_search => custom equalsTo()
- [ ] ugly const REGEX_XXX =>  class TileRegex
- [ ] static factory method such as fromString($s) force subclasses keep constructor signature => ?

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

rush 2 judge pinfu 6/3 3h

- [x] MeldTypes
- [ ] Hand.getMeldCompositions()
- [x] new a TileSequence

rush 3 refactor 6/4 5.5h 6/6 2.5h

- [x] refactor Tile&TileList
- [x] Enum
- [x] Tile: refactor, fromString()
- [x] TileList: refactor, fromString()
- [x] Meld: refactor
- [x] Singleton override return
- [x] fulfil test cases

rush 4 hand 6/7 3.0h
- [x] simple UI: TileList
- [x] simple UI: remove a Tile
- [x] Hand
- [x] readonly TileList&TileOrderedList
- [x] Meld fromString
- [x] MeldList fromString
- [x] given a TileList, analyze MeldType
- [x] refactor Meld, replace inheritance with association
- [ ] exposed/concealed triplet/kong

rush 5 hand UI
- [ ] simple UI: draw and discard

rush 6 round 6/5 2.5h
- [ ] overall flow
- [ ] Round

## note

round start

for one player
 show possible commands
  chou etc.
  discard 
 execute a command
 to next player 