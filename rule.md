# 文档

文档书写标准

- 英文术语以MIL为准，自创者标*。
- 叙述方式适合程序逻辑。

参考资料

- Mahjong International League/Riichi Competition Rules http://mahjong-mil.org/riichirules2016.pdf
- 天鳳 http://tenhou.net/man
- 日本プロ麻雀連盟/競技ルール http://www.ma-jan.or.jp/guide/game_rule.html
- wiki/麻雀のルール https://ja.wikipedia.org/wiki/麻雀のルール

# Saki的规则采用

点数与终局

- 配点：持点25000返点30000，顺位马10-20。
- 供托：每本场300点，游戏结束时转移给首位。
- 终了结算：按1000点四舍五入，按（点数，座次）排位。
- 打飞：有，有玩家小于0点时终了。
- 加时赛：东风战南入，东南战西入。有玩家满30000点终了。

进行规则

- 赤宝牌：有，包括：0m，0p，0s，共3张。（天凤：可选）
- 里宝牌：有，不可放弃。
- 明杠宝牌：有，成立时即翻后补牌，被抢杠时杠不成立不翻。

- 后付：有。
- 食断：有。（天凤：可选）
- 食替：无。

役规则

- 地方役：无。
- 人和：有，要求第一次自摸前。（天凤：无）
- 累计役满：有，13番以上。
- 累计多倍役满：有，13番以上时，役满倍数为floor(番数/13)。（天凤：无）
- 国士无双抢暗杠：无。
- 国士无双十三面待：有，双倍役满。（天凤：役满）
- 四暗刻单骑：有，双倍役满。（天凤：役满）
- 纯正九宝莲灯：有，双倍役满。（天凤：役满）

和牌规则

- 起和：1番。
- 自摸平和：有。
- 振听立直：有。
- 空听立直：有。
- 立直放冲：立直不成立，不支付立直棒。
- 立直后暗杠：有，要求不改变待牌。
- 两家和：结算，供托转移给上家，庄家和牌连庄。
- 包：大三元确定，大四喜确定。

流局规则

- 荒牌流局：庄家听牌连庄。
- 形式听牌：有，不考虑待牌数。（天凤：有，但纯手牌含4张的待牌不视为听牌）
- 不听罚符：总计3000点。
- 流局满贯：有。按满贯自摸和结算点数，多家同时成立时分别结算。庄家听牌连庄。
- 九种九牌：途中流局，连庄。
- 四风连打：途中流局，连庄。
- 四家立直：途中流局，连庄。
- 四开杠：途中流局，连庄。
- 三家和：途中流局，连庄。

# 术语

牌
- 牌，Tile：游戏的基本元素，分为数牌和字牌两种类别，分为34种，每种4张，共136张。

- 数牌，Suit：牌的两种类别之一，由花色和数值两种元素构成的牌，分为27种，每种4张，共108张。
- 花色，*Color：数牌的两种构成元素之一，包括：万Character、饼Circle、索Bamboo，共3种，分别记为m、p、s。
- 数值，Number：数牌的两种构成元素之一，包括：1、2、3、4、5、6、7、8、9，共9种。

- 字牌，Honour：牌的两种类别之一，不含数值的牌，共7种，每种4张，共28张。
- 风牌，Wind：字牌的两种类别之一，包括：东、南、西、北，共4种，分别记为E、S、W、N。
- 三元牌，Dragon：字牌的两种类别之一，包括：中、白、发，共3种，分别记为C、P、F。

- 老头牌，Term：数值为1或9的数牌，包括：1m、9m、1p、9p、1s、9s，共6种。
- 幺九牌，TermOrHonour：老头牌和字牌的总称，包括：1m、9m、1p、9p、1s、9s、E、S、W、N、C、P、F，共16种。

牌的组合，*Meld

- 对子，Pair：2张相同的牌的组合。
- 面子，Set：顺子、刻子、杠子的总称。
- 顺子，Chow：3张花色相同、数值连续的牌的组合。
- 刻子，Pung：3张相同的牌的组合。
- 杠子，Kong：4张相同的牌的组合。
- 明刻, Pung
- 暗刻, ConcealedPung
- 明杠, Kong
- 暗杠, ConcealedKong

局中的牌

- 游戏，Game
- 局，Round

- 场风，PrevailingWind
- 自风，SeatWind
- 庄家，Dealer
- 闲家，LeisureFamily

- 骰子，Dice：产生随机数1～6的道具，共2个。
- 牌墩，Stack：山牌的组成单位，由上、下两枚牌，各自的打开状态组成。
- 山牌，Wall
- 王牌，DeadWall

- 宝牌指示牌，Indicator：
- 宝牌，Dora：
- 里宝牌，*UraDora：
- 赤宝牌，*RedDora：
- 宝牌类型役，*DoraTypeYaku：

- 巡目，*CircleCount
- 当前自风，*CurrentSeatWind：
- 当前阶段，*CurrentPhase：个人阶段，公共阶段。
- 回合，Turn：（巡目，当前自风）。

- 手牌, Hand
- 宣告区，Melded

# 流程

## 单局的流程

空阶段，*NullPhase
- 进入：1.重置为默认值。
- 离开：1.进入初始阶段。

初始阶段，*InitPhase
- 进入：1.初始化。
- 离开：1.进入庄家的个人阶段。

初始化
1.洗牌，Mix the tiles
2.砌牌，Building the wall：玩家数=4时，每人面前共有17牌墩，即34张牌。以面向从左到右为正序。
  50..34
51      33
..      ..
67      17
  00..16

3.掷骰，Roll two dice：庄家掷骰2个得点数n，发牌玩家 = SeatWind n。
4.分牌，Break the wall：发牌起点=发牌玩家的逆序第n+1牌墩
5.发牌，The deal：由发牌起点逆序开始，按ESWN顺轮流取牌4次，每次取牌数4、4、4、1。
6.翻宝牌表示牌：由发牌玩家的逆序第n牌墩开始，顺序取7牌墩，作为王牌。翻开1枚宝牌表示牌。

（当前玩家，是否抽牌）的个人阶段，*PrivatePhase
- 进入：1.荒牌流局判定。2.修改当前玩家。3.等待当前玩家指令。
- 离开：1.进入公共阶段。

公共阶段，*PublicPhase
- 进入：1.等待其它玩家指令。
- 离开：1.途中流局判定。2.进入（下一玩家,抽牌）的个人阶段。

结束阶段，*OverPhase
- 进入：1.记录结果，修改分数。
- 离开：1.进入空阶段。

游戏结束判定
如果有点数<0的玩家，游戏结束
如果是AllLast或加时赛
    如果连庄
        如果有点数>=30000的玩家，庄家点数唯一最大，游戏结束
    如果非连庄
        如果是加时赛的最后一局，游戏结束
        如果有点数>=30000的玩家，游戏结束

## 指令类型

系统指令
- 抽牌，Draw：
- 补牌，*DrawReplacement：

Command：支持Command和string的互相转化（ParamDeclaration）；统一执行接口（executable(), execute()）。
->DebugCommand：目前仅标记。
->PlayerCommand：提供Actor便利函数；细分执行接口？；辅助Provider的接口。
-->PrivateCommand：目前仅标记。
-->PublicCommand：目前仅标记。
--->PublicClaimCommand

Command.executable()
- Command要求static::matchPhase()
- PlayerCommand要求static::matchActor()
- PlayerCommand::getExecutableList()利用上述两static函数来初步过滤

Command.execute()

PlayerCommand.getExecutableList()
1. matchPhaseAndActor
2. abstract getOtherParamsList()
3. new commands, keep executable ones.

个人指令，*PrivateCommand
- 打牌，Discard：hand -> discard, toPublic
- 暗杠，ConcealedKong：private -> declare, replace, stayPrivate
- 加杠，*ExtendKong：robbing logic, private -> declare, replace, stayPrivate
- 立直，Riichi：reachStatus, hand -> discard, toPublic
- 九种九牌流局，*NineNineDraw：toOver(result)
- 自摸和，Tsumo：toOver(result)

公共指令，*PublicCommand
- 跳过，Pass：
- 吃，Chow：public + target -> declare, toPrivate(actor, draw=false)
- 碰，Pung：public + target -> declare, toPrivate(actor, draw=false)
- 大明杠，Kong：public + target -> declare, toPrivate(actor, draw=false)
- 荣和，Ron：toOver(result)

## 公共阶段的指令决定

指令决定
1.可选指令集：pass，其它优先级不低于当前bufferCandidate的指令。
2.所有玩家发出1次可选指令。每次接收指令后，更新bufferCandidate，随之更新各玩家的可选指令集。
3.所有玩家发出过指令后，生成并执行最终指令。

优先级：ron>pung=kong>chow>pass
特殊处理
- passAll
- doubleRon
- tripleRon

## 公共阶段的无用指令跳过

// skip AI private actor
if private phase && actor.isAI
  execute random discard

// skip all public actor's needless single pass commands
if public phase && decider on
  commands = provider.get where public actor
    for all actor where commands=[pass] or actor.isAI
      execute pass

## 指令的前置条件

吃，Chow
- 指定牌+目标牌构成牌型
- 公共阶段，非河底
- 未立直
- 食断，Swap-calling：例如吃23s后不能打出1s或4s，手牌为1234s时不能用23s吃1s。

碰，Pung
- 指定牌+目标牌构成牌型
- 公共阶段，非河底
- 未立直

大明杠，Kong
- 指定牌+目标牌构成牌型
- 公共阶段，非河底
- 未立直
- 岭上牌残数>0

暗杠，ConcealedKong
- 指定牌+目标牌构成牌型
- 个人阶段，非海底
- 立直后暗杠：暗杠含目标牌，暗杠后听牌不变。
- 岭上牌残数>0

加杠，ExtendKong
- 指定牌+目标副露构成牌型
- 个人阶段，非海底
- 不可能立直
- 岭上牌残数>0

## 指令的执行流程

立直
1. 个人阶段，设目标牌，设公共阶段callback
2. 公共阶段
3. 离开公共阶段（非前往结束阶段时，设置立直状态和支付立直棒）

大明杠
1. 他家公共阶段，杠宣言成立
2. 离开公共阶段（无四开杠途流）
3. 杠者不摸牌个人阶段

暗杠
1. 杠者个人阶段，杠宣言成立

加杠
1. 杠者个人阶段，设抢杠目标牌
2. 抢杠公共阶段（仅限和牌指令）
3. 离开抢杠公共阶段（无四开杠途流）
4. 杠者不摸牌个人阶段
5. 杠宣言成立

# 流局的判定

## 荒牌流局，ExhaustiveDraw

进入个人阶段前判定。需要抽牌且牌山不存在牌时流局。

## 流局满贯，NagashiManganDraw

荒牌流局时，舍牌都是幺九牌，舍牌没有被他家鸣牌。

## 途中流局（主动）

玩家指令

- 九种九牌：第1回合，无玩家鸣牌，幺九牌的种类>9。

## 途中流局（被动）

公共阶段离开时判定。

种类

- 四风连打：第1巡，打出了4张同样的风牌，不存在吃碰杠宣言。
- 四开杠：场上有4个杠子，且它们属于至少2个玩家。杠指令执行时，在杠者打牌后的公共阶段离开时判定。

大明杠：上一公共（不判定），[个人]，公共（仅和），判定
暗杠：个人，个人，公共（仅和），判定
加杠：个人，抢杠公共（不判定），[个人]，公共（仅和），判定

- 四家立直：有4家立直。
- 三家和：有3家发出了和牌指令。

## 副露的表示

chow: fromRel=prev
- 排序后，把 目标牌 移动到 fromRel位置
- 45+6s: -6s,4s,5s
- 46+5s: -5s,4s,6s
- 56+4s: -4s,5s,6s

pung: fromRel=other
- 排序后，把 目标牌 移动到 fromRel位置
- 55s+0s: [-0s,5s,5s] [5s,-0s,5s] [5s,5s,-0s]

kong: fromRel=other
- 排序后，把 目标牌 移动到 fromRel位置
- 555s+0s: [-0s,5s,5s,5s] [5s,-0s,5s,5s] [5s,5s,5s,-0s]

extendKong: fromRel=other+self
- 在原json基础上，把 目标牌 放在 原fromRel位置
- 55s+0s+5s: [-5s,-0s,5s,5s] [5s,-5s,-0s,5s] [5s,5s,-5s,-0s]

concealedKong: fromRel=self
- 排序后，把 赤牌 交换到 pos=1，隐藏两端
- 5555s: O,5s,5s,O
- 5500s: O,0s,0s,O

# 和牌的判定

## 种类

- 荣和Ron
- 自摸和Tsumo
- 两家和*DoubleRon

## 和牌条件

- 有任意一种牌型。
- 有至少1番（不计宝牌）。
- 非振听。

## 牌型

- 41牌型：有4个面子，有1个对子。
- 七对子牌型：有7个不同的对子。
- 国士无双牌型：只有幺九牌，有所有幺九牌。

## 听牌，Tenpai

## 听牌类型

- 边张
- 嵌张
- 两面
- 单骑
- 地狱单骑
- 空听

## 振听，Furiten

符合其它和牌条件但不允许荣和的情形。

### 公开牌记录（*OpenHistory）

各回合、各玩家的舍牌和加杠牌的记录。用于振听判定。

### 振听目标牌清单（*FuritenTargetTileList）

公共阶段中，听牌玩家的振听目标牌清单是：

- 非两面待听牌时：目标牌。
- 两面待听牌时：涉及该目标牌的 两面待的 所有待牌。

用于振听判定。

## 振听目标牌，*FuritenTargetTile

振听目标牌清单中的牌。

### 振听条件

1.符合其它和牌条件。
2.荣和。注意自摸和不受振听影响。
3.符合以下任一条件

- 舍牌振听，*OpenFuriten：玩家的 公开牌记录中含有 任一振听目标牌。
- 立直振听，*RiichiFuriten：玩家的 立直宣言后的 任一公开牌记录中含有 任一振听目标牌。
- 同巡振听，*TemporaryFuriten：玩家的 上一次舍牌回合开始的 任一公开牌记录中含有 任一振听目标牌。注意“同巡”有特殊含义，不是“同一巡目”的意思。

## 点数的种类

- 番数，Fan：
- 符数，Minipoint，*Fu：
- 点数，Point：
- 点棒，Stick：点数标记，包括：100、1000、5000、1000，共4种，分为记为::::、O、:O:、O・:O:・O。
- 基本点

## 基本点的计算

番数和符数    | 等级    | 基本点
------------ | ------ | ---
1-2番        | 无      | 番 * (2 ^ (符 + 2))
3番，不满70符 | 无      | 番 * (2 ^ (符 + 2))
4番，不满30符 | 无      | 番 * (2 ^ (符 + 2))
3番，满70符   | 满贯    | 2000
4番，满30符   | 满贯    | 2000
5番          | 满贯    | 2000
6-7番        | 倍满    | 3000
8-10番       | 跳满    | 4000
11-12番      | 三倍满   | 6000
13-25番      | 役满    | 8000
26-38番      | 双倍役满 | 16000
39番以上      | 多倍役满 | 8000 * floor(番 / 13)

## 负担额的计算

负担额：基本额 + 场棒 + 立直棒。

术语

- 胜者：和牌的玩家。
- 胜者数：荣和时可能为 （1 到 玩家数-1），自摸和为 1。
- 败者：放冲或被自摸的玩家。
- 败者数：荣和时为 1，自摸和时为 玩家数-1。
- 无关者：不属于胜者和败者、没有分数变动的玩家。
- 无关者数：荣和时为 （0 到 玩家数-2），自摸和时为 0。

基本额（仅四人情形）

- 胜者收入
- 败者支付

场棒

- 场棒总额：300 * 本场数。
- 场棒负担额：胜者收入（场棒总额 / 胜者数），败者支付（场棒总额 / 败者数）。

立直棒

- 立直棒负担额：自摸和时胜者收入 所有立直棒，荣和时离败者最近的胜者收入 所有立直棒。其它玩家不受影响。

包

- 存在包时，自摸由包玩家全额支付（基本额+场棒），放冲由包玩家和放冲玩家各半支付（基本额+场棒）。

## 游戏结束结算

- 配点：第一局开始时，各玩家持有的点数。
- 原点：游戏终了结算时，用于丘计算的点数。
- 25000-30000：配点25000，原点30000，
- 顺位马10-20：游戏终了结算时，第4名向第1名支付20000点，第3名向第2名支付10000点。
- 丘：游戏终了结算时，第1名获得 （配点-原点）x玩家数 的点数。

## 符数，Fu

参考：http://ja.wikipedia.org/wiki/麻雀の得点計算#.E7.AC.A6.E3.81.AE.E8.A8.88.E7.AE.97

- 特殊：自摸平和=20，七对子=25，非自摸平和=30。
- 副底：+20。
- 面子：刻子或杠子+2，幺九x2，暗刻或暗杠x2，杠子x4。
- 雀头：自风+2，场风+2，三元牌+2。
- 待牌：中张+2，边张+2，单骑+2。
- 门清：+10。
- 自摸：+2。

## 役种，Yaku

### 1番役，OneFanYaku

- 立直，Riichi：任意牌型，门清，有立直宣告。
- 一发，*FirstTurnWin：任意牌型，门清，有立直宣告，记有效巡区间为“从（立直巡，下家）到（立直巡+1，自家）”，和牌巡在有效巡区间内，有效巡区间内没有吃碰杠宣告。
- 门清自摸，FullyConcealedHand：任意牌型，门清，自摸和。
- 平和，Pinfu：41牌型，门清，有4个顺子，有1个非幺九牌对子，听牌类型是两面待。
- 一杯口，PureDoubleChow：41牌型，门清，有至少2个相同的顺子。
- 断幺九，AllSimples：任意牌型，没有幺九牌。
- 役牌-中，*DragonPungRed：任意牌型，有1个中的刻子或杠子。
- 役牌-白，*DragonPungWhite：任意牌型，有1个白的刻子或杠子。
- 役牌-发，*DragonPungGreen：任意牌型，有1个发的刻子或杠子。
- 役牌-自风，SeatWind：任意牌型，有1个自风的刻子或杠子。
- 役牌-场风，PrevailingWind：任意牌型，有1个场风的刻子或杠子。
- 岭上开花，AfterAKong：任意牌型，在自家任意杠宣告后和牌，目标牌是岭上牌。
- 抢杠，RobbingAKong：任意牌型，在他家加杠宣告后和牌，目标牌是加杠宣告牌。
- 海底捞月，*BottomOfTheSeaMoon：任意牌型，自摸和，目标牌是最后一张牌。
- 河底摸鱼，*BottomOfTheSeaFish：任意牌型，荣和，目标牌是最后一张牌。

### 2番役，TwoFanYaku

- 双立直，*DoubleRiichi：任意牌型，门清，本局中已经宣告立直，且立直时为第一巡。不记立直。
- 七对子，SevenPairs：七对子牌型，门清，有7个不同的对子。符数固定为25符。
- 三色同顺，MixedTripleChow：41牌型，非门清减1番，有3个不同花色、相同数值的顺子。
- 一气通贯，PureStraight：41牌型，非门清减1番，有任意一种以下3个顺子的集合：123m+456m+789m，或者123p+456p+789p，或者123s+456s+789s。
- 混全带幺九，OutsideHand：41牌型，非门清减1番，有至少1个顺子，每个面子和对子都有至少1张幺九牌。
- 三色同刻，TriplePung：41牌型，非门清减1番，有3个不同花色、相同数值的刻子。
- 三暗刻，ThreeConcealedPungs：41牌型，有3个暗刻。
- 三杠子，ThreeKongs：必定41牌型，有3个杠子。
- 对对和，AllPungs：41牌型，有4个刻子或杠子，有1个对子。
- 小三元，LittleThreeDragons：41牌型，有2个三元牌的刻子或杠子，有1个三元牌对子。
- 混老头，*AllTerminalsAndHonours：任意牌型，只有幺九牌。

### 3番役，ThreeFanYaku

- 二杯口，TwicePureDoubleChow：41牌型，门清，有4个顺子，其中有2个相同、另外2个相同且和前2个不同。
- 混一色，HalfFlush：任意牌型，有至少1张字牌，除字牌外只有同1种花色数牌。
- 纯全带幺九，TerminalsInAllSets：41牌型，非门清减1番，有至少1个顺子，每个面子和对子都有至少1张老头牌。

### 6番役，SixFanYaku

- 清一色，FullFlush：任意牌型，只有同1种花色的数牌。

### 役满，Yakuman

- 国士无双，ThirteenOrphans：国士无双牌型，门清。
- 九莲宝灯，NineGates：必定41牌型，门清，只有同一种花色的数牌，有1112345678999这13种数牌。
- 天和，BlessingOfHeaven：任意牌型，第一巡，不存在吃碰杠宣言，个人阶段，庄家。
- 地和，BlessingOfEarth：任意牌型，第一巡，不存在吃碰杠宣言，个人阶段，闲家。
- 人和，BlessingOfMan：任意牌型，第一巡，不存在吃碰杠宣言，公共阶段，当前玩家<目标玩家。
- 四暗刻，FourConcealedPungs：41牌型，门清，有4个暗刻。
- 四杠子，FourKongs：必定41牌型，有4个杠子。
- 绿一色，AllGreen：必定41牌型，只有以下牌：2s、3s、4s、6s、8s、发。
- 字一色，AllTerminals：任意牌型，只有字牌。
- 清老头，AllHonours：任意牌型，只有老头牌。
- 大三元，BigThreeDragons：41牌型，有3个三元牌的刻子或杠子。
- 小四喜，LittleFourWinds：41牌型，有3个风牌的的刻子或杠子，有1个风牌的对子。
- 大四喜，BigFourWinds：41牌型，有4个风牌的的刻子或杠子。

### 双倍役满，*DoubleYakuMan

- 国士无双十三面待，*PureThirteenOrphans：国士无双牌型，门清，目标牌是持有数为2的牌。不计国士无双。
- 纯正九莲宝灯，*PureNineGates：必定41牌型，门清，只有同一种花色的数牌，手牌除去目标牌后有1112345678999这13种数牌。不计九莲宝灯。
- 四暗刻单骑，*PureFourConcealedPungs：41牌型，门清，有4个暗刻，单骑听牌。不计四暗刻。