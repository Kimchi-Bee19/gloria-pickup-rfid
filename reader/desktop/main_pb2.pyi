from google.protobuf import descriptor as _descriptor
from google.protobuf import message as _message
from typing import ClassVar as _ClassVar, Optional as _Optional

DESCRIPTOR: _descriptor.FileDescriptor

class StudentDepartureMessage(_message.Message):
    __slots__ = ("timestamp", "tagId")
    TIMESTAMP_FIELD_NUMBER: _ClassVar[int]
    TAGID_FIELD_NUMBER: _ClassVar[int]
    timestamp: int
    tagId: bytes
    def __init__(self, timestamp: _Optional[int] = ..., tagId: _Optional[bytes] = ...) -> None: ...

class VehicleArrivalMessage(_message.Message):
    __slots__ = ("timestamp", "tagId")
    TIMESTAMP_FIELD_NUMBER: _ClassVar[int]
    TAGID_FIELD_NUMBER: _ClassVar[int]
    timestamp: int
    tagId: bytes
    def __init__(self, timestamp: _Optional[int] = ..., tagId: _Optional[bytes] = ...) -> None: ...

class ReaderStatusMessage(_message.Message):
    __slots__ = ("isOnline", "timestamp", "clientId")
    ISONLINE_FIELD_NUMBER: _ClassVar[int]
    TIMESTAMP_FIELD_NUMBER: _ClassVar[int]
    CLIENTID_FIELD_NUMBER: _ClassVar[int]
    isOnline: bool
    timestamp: int
    clientId: str
    def __init__(self, isOnline: bool = ..., timestamp: _Optional[int] = ..., clientId: _Optional[str] = ...) -> None: ...
